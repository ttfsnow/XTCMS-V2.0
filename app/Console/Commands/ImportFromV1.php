<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\NewsClass;
use App\Models\NewsContent;
use App\Models\SiteConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportFromV1 extends Command
{
    protected $signature = 'xtcms:import-v1
                            {--host=127.0.0.1 : V1 数据库主机}
                            {--port=3306 : V1 数据库端口}
                            {--database=xtcms : V1 数据库名}
                            {--username=root : V1 数据库用户}
                            {--password= : V1 数据库密码}
                            {--prefix=xt_ : V1 数据表前缀}';

    protected $description = '将 XTCMS V1.0 的数据（管理员/分类/文章/站点配置）导入 V2.0';

    public function handle(): int
    {
        config(['database.connections.v1' => [
            'driver'    => 'mysql',
            'host'      => $this->option('host'),
            'port'      => (int) $this->option('port'),
            'database'  => $this->option('database'),
            'username'  => $this->option('username'),
            'password'  => (string) $this->option('password'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ]]);

        DB::purge('v1');

        try {
            DB::connection('v1')->selectOne('select 1');
        } catch (\Throwable $e) {
            $this->error('无法连接 V1 数据库：'.$e->getMessage());

            return self::FAILURE;
        }

        $prefix = $this->option('prefix');

        $this->importAdmin($prefix);
        $this->importClasses($prefix);
        $this->importContents($prefix);
        $this->importConfig($prefix);

        $this->info('导入完成。旧的管理员密码（MD5）已保留，登录时将自动校验并升级为 bcrypt 加密。');

        return self::SUCCESS;
    }

    private function importAdmin(string $prefix): void
    {
        $rows = DB::connection('v1')->table($prefix.'admin')->get();
        $count = 0;

        foreach ($rows as $row) {
            Admin::query()->updateOrCreate(
                ['username' => $row->username],
                ['password' => $row->password],
            );
            $count++;
        }

        $this->line("管理员：导入 {$count} 条");
    }

    private function importClasses(string $prefix): void
    {
        $columns = DB::connection('v1')->getSchemaBuilder()->getColumnListing($prefix.'newsclass');

        // 兼容两种历史结构：p_id/name/path（实际运行版）与 f_id/cname（安装脚本版）
        $parentCol = in_array('p_id', $columns) ? 'p_id' : 'f_id';
        $nameCol   = in_array('name', $columns) ? 'name' : 'cname';
        $pathCol   = in_array('path', $columns) ? 'path' : null;

        $rows = DB::connection('v1')->table($prefix.'newsclass')->orderBy('id')->get();
        $count = 0;

        foreach ($rows as $row) {
            $path = $pathCol !== null
                ? (string) ($row->{$pathCol} ?? '0')
                : $this->guessPath((int) $row->{$parentCol});

            NewsClass::query()->updateOrCreate(
                ['id' => $row->id],
                [
                    'parent_id'  => (int) $row->{$parentCol},
                    'name'       => $row->{$nameCol},
                    'path'       => $path !== '' ? $path : '0',
                ],
            );
            $count++;
        }

        $this->line("分类：导入 {$count} 条");
    }

    private function guessPath(int $parentId): string
    {
        if ($parentId === 0) {
            return '0';
        }

        $parent = NewsClass::query()->find($parentId);

        return $parent ? $parent->bpath : '0';
    }

    private function importContents(string $prefix): void
    {
        $rows = DB::connection('v1')->table($prefix.'newscontent')->orderBy('id')->get();
        $count = 0;

        foreach ($rows as $row) {
            NewsContent::query()->updateOrCreate(
                ['id' => $row->id],
                [
                    'cid'      => (int) $row->cid,
                    'title'    => $row->title,
                    'author'   => $row->author ?? null,
                    // V1 安装脚本拼写为 keywrod，运行代码读取 keyword，两者兼容
                    'keyword'  => $row->keyword ?? $row->keywrod ?? null,
                    'content'  => $row->content,
                    'date_time' => $row->date_time
                        ? date('Y-m-d H:i:s', (int) $row->date_time)
                        : now(),
                ],
            );
            $count++;
        }

        $this->line("文章：导入 {$count} 条");
    }

    private function importConfig(string $prefix): void
    {
        $rows = DB::connection('v1')->table($prefix.'config')->get();

        if ($rows->isEmpty()) {
            return;
        }

        // V1 的 config 表为无键名行存储，第 1 行为站点标题
        $keyMap = ['site_name', 'site_subtitle', 'footer_about', 'contact_email'];

        foreach ($rows->values() as $index => $row) {
            // 若 name 列本身有语义则直接使用，否则按位置映射
            $name = isset($row->name) && is_string($row->name) && $row->name !== ''
                ? $row->name
                : ($keyMap[$index] ?? "legacy_{$index}");

            SiteConfig::put($name, (string) $row->values);
        }

        $this->line('站点配置：导入 '.$rows->count().' 条');
    }
}

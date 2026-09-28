<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

class BackupDatabase extends Command
{
    protected $signature = 'xtcms:backup {--keep=10 : 最多保留的备份文件数量}';

    protected $description = '备份数据库到 storage/app/backups（纯 PHP 导出，无需 mysqldump）';

    public function handle(): int
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? '') !== 'mysql') {
            $this->error('当前仅支持 MySQL 数据库备份');

            return self::FAILURE;
        }

        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $dir.'/'.($config['database'] ?? 'database').'-'.now()->format('Ymd-His').'.sql';
        $pdo = DB::connection()->getPdo();

        $out = $this->header($config, $pdo);
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $out .= $this->dumpTable($pdo, $table);
        }

        file_put_contents($file, $out);
        $this->info("备份完成：{$file}（".round(filesize($file) / 1024, 1).' KB）');

        $this->rotate($dir, max(1, (int) $this->option('keep')));

        return self::SUCCESS;
    }

    private function header(array $config, PDO $pdo): string
    {
        $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

        return "-- XTCMS V2.0 数据库备份\n"
            .'-- 时间：'.now()->format('Y-m-d H:i:s')."\n"
            .'-- 数据库：'.$config['database']."（MySQL {$version}）\n\n"
            ."SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";
    }

    private function dumpTable(PDO $pdo, string $table): string
    {
        $out = "-- ----------------------------\n-- 表结构：{$table}\n-- ----------------------------\n";
        $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
        $out .= "DROP TABLE IF EXISTS `{$table}`;\n".$create[1].";\n\n";

        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows === []) {
            return $out."\n";
        }

        $out .= "-- 表数据：{$table}（".count($rows)." 行）\n";
        $columns = array_keys($rows[0]);
        $columnSql = '`'.implode('`, `', $columns).'`';

        foreach (array_chunk($rows, 100) as $chunk) {
            $values = [];
            foreach ($chunk as $row) {
                $escaped = array_map(fn ($v) => match (true) {
                    $v === null => 'NULL',
                    is_int($v) || is_float($v) => (string) $v,
                    default => $pdo->quote((string) $v),
                }, $row);

                $values[] = '('.implode(', ', $escaped).')';
            }
            $out .= "INSERT INTO `{$table}` ({$columnSql}) VALUES\n".implode(",\n", $values).";\n";
        }

        return $out."\n";
    }

    /** 只保留最近 $keep 个备份文件 */
    private function rotate(string $dir, int $keep): void
    {
        $files = glob($dir.'/*.sql');

        if (count($files) <= $keep) {
            return;
        }

        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        foreach (array_slice($files, $keep) as $old) {
            @unlink($old);
            $this->line('已清理旧备份：'.basename($old));
        }
    }
}

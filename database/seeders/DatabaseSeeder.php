<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\NewsClass;
use App\Models\NewsContent;
use App\Models\SiteConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Admin::query()->updateOrCreate(
            ['username' => 'admin'],
            ['password' => Hash::make('admin123')],
        );

        foreach ([
            'site_name'     => 'XTCMS 站点',
            'site_subtitle' => '这是一个使用 XTCMS V2.0 搭建的博客',
            'footer_about'  => 'XTCMS V2.0 —— 基于 Laravel 11 重新构建的轻量级内容管理系统。',
            'contact_email' => '',
        ] as $name => $value) {
            SiteConfig::put($name, $value);
        }

        $tech = NewsClass::query()->create(['parent_id' => 0, 'name' => '技术笔记', 'path' => '0']);
        $life = NewsClass::query()->create(['parent_id' => 0, 'name' => '生活随笔', 'path' => '0']);

        NewsContent::query()->create([
            'cid'      => $tech->id,
            'title'    => 'XTCMS V2.0 发布：从 PHP 5 到 Laravel 11',
            'author'   => 'admin',
            'keyword'  => 'XTCMS,Laravel',
            'content'  => '<p>XTCMS V2.0 使用 Laravel 11 + MySQL 8 + TinyMCE 6 重新构建，修复了 V1 存在的 SQL 注入、明文密码等安全问题，前台全面支持响应式布局。</p>',
            'date_time' => now(),
        ]);

        NewsContent::query()->create([
            'cid'      => $life->id,
            'title'    => '欢迎使用 XTCMS',
            'author'   => 'admin',
            'keyword'  => '随笔',
            'content'  => '<p>这是第一篇示例文章，你可以登录后台（/admin，默认账号 admin / admin123）进行修改或删除。</p>',
            'date_time' => now(),
        ]);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('标签名');
            $table->timestamps();
        });

        // 文章-标签关联表（沿用 V1 风格表名 newscontent / newstag）
        Schema::create('newstag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('news_id')->index()->comment('文章 newscontent.id');
            $table->unsignedBigInteger('tag_id')->index()->comment('标签 tags.id');
            $table->unique(['news_id', 'tag_id']);
        });

        // 一次性数据迁移：把现有文章的 keyword（英文逗号分隔）导入为真实标签
        // keyword 字段本身保留，继续作为 SEO meta 使用
        DB::table('newscontent')
            ->whereNotNull('keyword')
            ->where('keyword', '!=', '')
            ->orderBy('id')
            ->get(['id', 'keyword'])
            ->each(function ($article) {
                collect(explode(',', (string) $article->keyword))
                    ->map(fn ($name) => trim($name))
                    ->filter()
                    ->unique()
                    ->each(function (string $name) use ($article) {
                        $tagId = DB::table('tags')->where('name', $name)->value('id');

                        if (! $tagId) {
                            $tagId = DB::table('tags')->insertGetId([
                                'name'       => $name,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        DB::table('newstag')->insertOrIgnore([
                            'news_id' => $article->id,
                            'tag_id'  => $tagId,
                        ]);
                    });
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('newstag');
        Schema::dropIfExists('tags');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newscontent', function (Blueprint $table) {
            $table->string('summary', 500)->nullable()->after('keyword')->comment('摘要，留空时前台自动截取正文');
            $table->unsignedTinyInteger('status')->default(1)->after('content')->comment('0=草稿 1=已发布');
            $table->index(['status', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('newscontent', function (Blueprint $table) {
            $table->dropIndex(['status', 'id']);
            $table->dropColumn(['summary', 'status']);
        });
    }
};

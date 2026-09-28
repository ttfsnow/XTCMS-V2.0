<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newscontent', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cid')->index()->comment('所属分类 newsclass.id');
            $table->string('title', 200);
            $table->string('author', 50)->nullable();
            $table->string('keyword', 100)->nullable();
            $table->mediumText('content');
            $table->dateTime('date_time')->index();
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();

            $table->index(['cid', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newscontent');
    }
};

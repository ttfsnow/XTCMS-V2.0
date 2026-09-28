<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 文章标签：与 SEO 关键词（newscontent.keyword）是两个概念。
 * 标签用于读者端的内容组织与发现，多对多关联 newscontent。
 */
class Tag extends Model
{
    protected $fillable = ['name'];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(NewsContent::class, 'newstag', 'tag_id', 'news_id');
    }
}

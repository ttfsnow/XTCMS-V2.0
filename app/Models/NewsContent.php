<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NewsContent extends Model
{
    protected $table = 'newscontent';

    protected $fillable = ['cid', 'title', 'author', 'keyword', 'content', 'date_time', 'summary', 'status'];

    protected $casts = [
        'date_time' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsClass::class, 'cid');
    }

    /** 文章标签（多对多，独立于 SEO 关键词 keyword 字段） */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'newstag', 'news_id', 'tag_id');
    }

    /** 仅已发布的文章（前台可见） */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('id');
    }

    /** 摘要：优先使用手动填写的 summary，否则去掉 HTML 标签后截取正文 */
    public function excerpt(int $length = 240): string
    {
        if (trim((string) $this->summary) !== '') {
            return $this->summary;
        }

        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($this->content)));

        return mb_strimwidth($text, 0, $length * 2, $text ? '...' : '');
    }

    /** V1 风格的标题截断 */
    public function shortTitle(int $length = 28): string
    {
        return mb_strimwidth($this->title, 0, $length * 2, mb_strlen($this->title) > $length ? '...' : '');
    }
}

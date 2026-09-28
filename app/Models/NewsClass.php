<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsClass extends Model
{
    protected $table = 'newsclass';

    protected $fillable = ['parent_id', 'name', 'path', 'sort_order'];

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(NewsContent::class, 'cid');
    }

    /** 当前节点的完整路径标识（兼容 V1 的 bpath 方案）：父路径-自身ID */
    public function getBpathAttribute(): string
    {
        return $this->path.'-'.$this->id;
    }

    /** 按 bpath 排序即为树的先序遍历顺序 */
    public function scopeTree(Builder $query): Builder
    {
        return $query->orderByRaw("concat(path, '-', id) asc");
    }

    public function scopeTop(Builder $query): Builder
    {
        return $query->where('parent_id', 0);
    }

    /** 计算 path 值：顶级为 '0'，子级为父级 bpath */
    public static function resolvePath(?self $parent): string
    {
        return $parent ? $parent->bpath : '0';
    }

    /** 所有后代分类的 id（含自身） */
    public function descendantIds(): array
    {
        $ids = self::query()
            ->where('path', 'like', $this->bpath.'-%')
            ->pluck('id')
            ->all();

        $ids[] = $this->id;

        return $ids;
    }
}

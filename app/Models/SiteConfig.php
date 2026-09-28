<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * 站点配置（key-value），继承自 V1 的 config 表。
 * 约定键名：site_name / site_subtitle / footer_about / contact_email
 */
class SiteConfig extends Model
{
    protected $table = 'config';

    protected $primaryKey = 'name';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['name', 'value'];

    public static function settings(): Collection
    {
        return static::query()->pluck('value', 'name');
    }

    public static function get(string $key, string $default = ''): string
    {
        $row = static::query()->find($key);

        return $row?->value ?? $default;
    }

    public static function put(string $key, string $value): void
    {
        static::query()->updateOrCreate(['name' => $key], ['value' => $value]);
    }
}

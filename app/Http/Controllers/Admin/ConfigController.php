<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfigController extends Controller
{
    public const KEYS = [
        'site_name'     => '站点名称',
        'site_subtitle' => '站点副标题',
        'footer_about'  => '页脚介绍',
        'contact_email' => '联系邮箱',
        'custom_css'    => '自定义 CSS',
        'head_code'     => '头部代码（统计/验证等，原样注入 <head>）',
    ];

    /** 允许较长内容的键 */
    private const LONG_KEYS = ['custom_css', 'head_code', 'footer_about'];

    public function edit(): View
    {
        return view('admin.config.index', [
            'keys'    => self::KEYS,
            'setting' => SiteConfig::settings(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (array_keys(self::KEYS) as $key) {
            $rules[$key] = in_array($key, self::LONG_KEYS, true)
                ? ['nullable', 'string', 'max:20000']
                : ['nullable', 'string', 'max:255'];
        }

        $data = $request->validate($rules);

        foreach ($data as $key => $value) {
            SiteConfig::put($key, (string) $value);
        }

        return back()->with('status', '站点配置已保存');
    }
}

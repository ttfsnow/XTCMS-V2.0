@extends('layouts.admin')

@section('title', '站点配置')

@section('content')
    <h1 class="text-xl font-bold mb-6">站点配置</h1>

    <form action="{{ route('admin.config.update') }}" method="post" class="bg-white rounded-lg border border-gray-200 p-6 space-y-5 max-w-2xl">
        @csrf
        @method('PUT')
        @foreach ($keys as $key => $label)
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ $label }}（{{ $key }}）</label>
                @if (in_array($key, ['footer_about', 'custom_css', 'head_code']))
                    <textarea name="{{ $key }}" rows="{{ $key === 'footer_about' ? 3 : 6 }}"
                              class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-mono {{ $key !== 'footer_about' ? 'bg-gray-50' : '' }}">{{ $setting->get($key) }}</textarea>
                    @if ($key === 'custom_css')
                        <p class="text-xs text-gray-400 mt-1">将原样注入前台 &lt;style&gt; 标签，可覆盖主题样式（如字体、颜色）</p>
                    @elseif ($key === 'head_code')
                        <p class="text-xs text-gray-400 mt-1">将原样注入前台 &lt;head&gt;，适合放统计脚本、站长验证码等（仅管理员可编辑，请确保内容可信）</p>
                    @endif
                @else
                    <input type="{{ $key === 'contact_email' ? 'email' : 'text' }}" name="{{ $key }}"
                           value="{{ $setting->get($key) }}" maxlength="255"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @endif
            </div>
        @endforeach

        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
            保存配置
        </button>
    </form>
@endsection

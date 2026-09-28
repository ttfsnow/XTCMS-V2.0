<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '后台管理') - XTCMS V2.0</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
    @stack('scripts')
</head>
<body class="min-h-screen flex bg-gray-100 text-gray-800">
<aside class="w-56 shrink-0 bg-gray-900 text-gray-300 flex flex-col min-h-screen">
    <div class="px-5 py-5 border-b border-gray-800">
        <p class="text-white font-bold">XTCMS V2.0</p>
        <p class="text-xs text-gray-500 mt-1">{{ session('admin_name') }} · 后台管理</p>
    </div>
    <nav class="flex-1 p-3 space-y-1 text-sm">
        @php
            $nav = [
                ['admin.dashboard', '仪表盘', route('admin.dashboard')],
                ['admin.news.index', '文章管理', route('admin.news.index')],
                ['admin.news-class.index', '分类管理', route('admin.news-class.index')],
                ['admin.tags.index', '标签管理', route('admin.tags.index')],
                ['admin.config.edit', '站点配置', route('admin.config.edit')],
                ['admin.password.edit', '修改密码', route('admin.password.edit')],
            ];
        @endphp
        @foreach ($nav as [$name, $label, $url])
            <a href="{{ $url }}"
               class="block px-3 py-2 rounded-md {{ request()->routeIs($name) ? 'bg-blue-600 text-white' : 'hover:bg-gray-800 hover:text-white' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>
    <div class="p-3 border-t border-gray-800 text-sm">
        <a href="{{ route('front.index') }}" target="_blank" class="block px-3 py-2 rounded-md hover:bg-gray-800 hover:text-white">
            查看站点 ↗
        </a>
        <form action="{{ route('admin.logout') }}" method="post">
            @csrf
            <button type="submit" class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-800 hover:text-white">退出登录</button>
        </form>
    </div>
</aside>

<main class="flex-1 min-w-0 p-6 lg:p-8">
    @if (session('status'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('status') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>

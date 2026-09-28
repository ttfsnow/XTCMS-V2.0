<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $site->get('site_name', 'XTCMS'))</title>
    <meta name="description" content="@yield('meta_description', $site->get('site_subtitle'))">
    <meta name="keywords" content="@yield('meta_keywords')">
    <link rel="alternate" type="application/rss+xml" title="{{ $site->get('site_name', 'XTCMS') }} RSS" href="{{ route('front.feed') }}">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📝</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    @if (trim((string) $site->get('custom_css')) !== '')
        <style>{!! $site->get('custom_css') !!}</style>
    @endif
    {!! $site->get('head_code') !!}
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-gray-800">
<header class="bg-white border-b border-gray-200">
    <div class="max-w-6xl mx-auto px-4 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('front.index') }}" class="text-2xl font-bold tracking-tight text-gray-900">
                {{ $site->get('site_name', 'XTCMS') }}
            </a>
            <p class="text-sm text-gray-500 mt-1">{{ $site->get('site_subtitle') }}</p>
        </div>
        <nav class="flex flex-wrap gap-1">
            <a href="{{ route('front.index') }}"
               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('front.index') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                博客主页
            </a>
            <a href="{{ route('front.archive') }}"
               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('front.archive') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                归档
            </a>
            @foreach ($navClasses as $class)
                <a href="{{ route('front.category', $class) }}"
                   class="px-3 py-2 rounded-md text-sm font-medium {{ request()->route('newsclass')?->id === $class->id ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    {{ $class->name }}
                </a>
            @endforeach
        </nav>
    </div>
</header>

<main class="flex-1 w-full max-w-6xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            @yield('content')
        </div>
        <aside class="space-y-6">
            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <form action="{{ route('front.search') }}" method="get" class="flex gap-2">
                    <input type="text" name="q" value="{{ request()->query('q') }}" placeholder="搜索文章..."
                           class="flex-1 min-w-0 rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">搜索</button>
                </form>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <h3 class="font-semibold mb-3">近期文章</h3>
                <ul class="space-y-2 text-sm">
                    @forelse ($recentArticles as $recent)
                        <li>
                            <a href="{{ route('front.show', $recent) }}" class="text-gray-600 hover:text-blue-600"
                               title="{{ $recent->title }}">{{ $recent->shortTitle() }}</a>
                        </li>
                    @empty
                        <li class="text-gray-400">暂无文章</li>
                    @endforelse
                </ul>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <h3 class="font-semibold mb-3">分类目录</h3>
                <ul class="space-y-2 text-sm">
                    @forelse ($navClasses as $class)
                        <li>
                            <a href="{{ route('front.category', $class) }}" class="text-gray-600 hover:text-blue-600">
                                {{ $class->name }}
                            </a>
                        </li>
                    @empty
                        <li class="text-gray-400">暂无分类</li>
                    @endforelse
                </ul>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <h3 class="font-semibold mb-3">文章归档</h3>
                <ul class="space-y-2 text-sm">
                    @forelse ($archiveYears as $item)
                        <li class="flex items-center justify-between">
                            <a href="{{ route('front.archive') }}#year-{{ $item['year'] }}" class="text-gray-600 hover:text-blue-600">
                                {{ $item['year'] }} 年
                            </a>
                            <span class="text-xs text-gray-400">{{ $item['count'] }} 篇</span>
                        </li>
                    @empty
                        <li class="text-gray-400">暂无文章</li>
                    @endforelse
                </ul>
            </div>

            @if ($tagCloud->isNotEmpty())
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="font-semibold mb-3">标签云</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($tagCloud as $tag)
                            <a href="{{ route('front.tag', ['tag' => $tag['name']]) }}"
                               class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-blue-50 hover:text-blue-600"
                               title="{{ $tag['count'] }} 篇文章">
                                {{ $tag['name'] }} <span class="text-gray-400">{{ $tag['count'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
</main>

<footer class="bg-gray-900 text-gray-300 mt-8">
    <div class="max-w-6xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-3 gap-8 text-sm">
        <div>
            <h4 class="font-semibold text-white mb-2">{{ $site->get('site_name', 'XTCMS') }}</h4>
            <p class="leading-relaxed text-gray-400">{{ $site->get('footer_about') }}</p>
        </div>
        <div>
            <h4 class="font-semibold text-white mb-2">导航</h4>
            <ul class="space-y-1 text-gray-400">
                <li><a href="{{ route('front.index') }}" class="hover:text-white">博客主页</a></li>
                <li><a href="{{ route('front.archive') }}" class="hover:text-white">文章归档</a></li>
                <li><a href="{{ route('front.feed') }}" class="hover:text-white">RSS 订阅</a></li>
                @foreach ($navClasses as $class)
                    <li><a href="{{ route('front.category', $class) }}" class="hover:text-white">{{ $class->name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div>
            <h4 class="font-semibold text-white mb-2">联系我</h4>
            @if ($site->get('contact_email'))
                <p class="text-gray-400">Email：{{ $site->get('contact_email') }}</p>
            @endif
            <p class="text-gray-500 mt-2">&copy; {{ date('Y') }} {{ $site->get('site_name', 'XTCMS') }} · Powered by XTCMS V2.0</p>
        </div>
    </div>
</footer>
</body>
</html>

@extends('layouts.admin')

@section('title', '仪表盘')

@section('content')
    <h1 class="text-xl font-bold mb-6">仪表盘</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-xs text-gray-500">文章总数</p>
            <p class="text-3xl font-bold mt-2 text-blue-600">{{ $articleCount }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <p class="text-xs text-gray-500">分类总数</p>
            <p class="text-3xl font-bold mt-2 text-green-600">{{ $categoryCount }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-5 flex flex-col justify-between">
            <p class="text-xs text-gray-500">快捷操作</p>
            <div class="mt-3 space-y-2 text-sm">
                <a href="{{ route('admin.news.create') }}" class="block rounded-md bg-blue-600 px-3 py-2 text-center text-white hover:bg-blue-700">撰写新文章</a>
                <a href="{{ route('admin.news-class.index') }}" class="block rounded-md border border-gray-300 px-3 py-2 text-center hover:bg-gray-50">管理分类</a>
            </div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-5 text-xs text-gray-500">
            <p class="text-sm font-semibold text-gray-800 mb-2">环境信息</p>
            <p>PHP {{ PHP_VERSION }}</p>
            <p class="mt-1">Laravel 11</p>
            <p class="mt-1">MySQL 8 · TinyMCE 6</p>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-200 font-semibold text-sm">最近发布的文章</div>
        <ul class="divide-y divide-gray-100 text-sm">
            @forelse ($latestArticles as $article)
                <li class="px-5 py-3 flex items-center justify-between gap-4">
                    <a href="{{ route('admin.news.edit', $article) }}" class="truncate hover:text-blue-600">{{ $article->title }}</a>
                    <span class="shrink-0 text-xs text-gray-400">{{ $article->date_time->format('Y-m-d H:i') }}</span>
                </li>
            @empty
                <li class="px-5 py-6 text-center text-gray-400">暂无文章</li>
            @endforelse
        </ul>
    </div>
@endsection

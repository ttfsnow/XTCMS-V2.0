@extends('layouts.admin')

@section('title', '文章管理')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold">文章管理</h1>
        <a href="{{ route('admin.news.create') }}"
           class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            撰写新文章
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200">
        <form action="{{ route('admin.news.index') }}" method="get"
              class="px-5 py-4 border-b border-gray-200 flex flex-wrap gap-3 items-center text-sm">
            <input type="text" name="q" value="{{ request()->query('q') }}" placeholder="按标题搜索"
                   class="rounded-md border border-gray-300 px-3 py-2 text-sm w-56">
            <select name="cid" class="rounded-md border border-gray-300 px-3 py-2 text-sm bg-white">
                <option value="">全部分类</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" {{ request()->query('cid') == $class->id ? 'selected' : '' }}
                        @if ($class->parent_id !== 0)
                            data-indent
                        @endif>
                        @if ($class->parent_id !== 0)　┗ @endif{{ $class->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md border border-gray-300 px-4 py-2 hover:bg-gray-50">筛选</button>
        </form>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-left text-xs">
                <tr>
                    <th class="px-5 py-3">标题</th>
                    <th class="px-5 py-3">状态</th>
                    <th class="px-5 py-3">分类</th>
                    <th class="px-5 py-3">作者</th>
                    <th class="px-5 py-3">发布时间</th>
                    <th class="px-5 py-3 text-right">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($articles as $article)
                    <tr>
                        <td class="px-5 py-3 max-w-xs">
                            <span class="block truncate" title="{{ $article->title }}">{{ $article->title }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @if ($article->status === 1)
                                <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs text-green-700 border border-green-200">已发布</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500 border border-gray-200">草稿</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ $article->category?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $article->author ?: '—' }}</td>
                        <td class="px-5 py-3 text-gray-400 text-xs">{{ $article->date_time->format('Y-m-d H:i') }}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('front.show', $article) }}" target="_blank" class="text-gray-500 hover:underline mr-3">预览</a>
                            <a href="{{ route('admin.news.edit', $article) }}" class="text-blue-600 hover:underline mr-3">编辑</a>
                            <form action="{{ route('admin.news.destroy', $article) }}" method="post" class="inline"
                                  onsubmit="return confirm('确定删除文章「{{ $article->title }}」吗？')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">删除</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">暂无文章</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4">
            {{ $articles->links() }}
        </div>
    </div>
@endsection

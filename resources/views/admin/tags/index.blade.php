@extends('layouts.admin')

@section('title', '标签管理')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold">标签管理</h1>
        <p class="text-sm text-gray-500">共 {{ $tags->count() }} 个标签</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-lg border border-gray-200">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left text-xs">
                    <tr>
                        <th class="px-5 py-3">标签名称</th>
                        <th class="px-5 py-3">关联文章</th>
                        <th class="px-5 py-3 text-right">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($tags as $tag)
                        <tr>
                            <td class="px-5 py-3">
                                <form action="{{ route('admin.tags.update', $tag) }}" method="post" class="flex items-center gap-2"
                                      onsubmit="return confirm('确定重命名标签「{{ $tag->name }}」吗？')">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $tag->name }}" maxlength="50" required
                                           class="rounded-md border border-gray-300 px-3 py-1.5 text-sm w-48">
                                    <button type="submit" class="text-blue-600 hover:underline shrink-0">重命名</button>
                                </form>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $tag->articles_count }} 篇</td>
                            <td class="px-5 py-3 text-right">
                                <form action="{{ route('admin.tags.destroy', $tag) }}" method="post" class="inline"
                                      onsubmit="return confirm('确定删除标签「{{ $tag->name }}」吗？它将从 {{ $tag->articles_count }} 篇文章上移除，文章本身不受影响。')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">删除</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-gray-400">暂无标签，在右侧创建第一个吧。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6 h-fit">
            <h2 class="font-bold mb-4">新建标签</h2>
            <form action="{{ route('admin.tags.store') }}" method="post" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 mb-1">标签名称 <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required maxlength="50" placeholder="如：Laravel、随笔"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                    创建标签
                </button>
                <p class="text-xs text-gray-400 leading-relaxed">
                    创建后即可在「撰写新文章」页面勾选使用。删除标签不会影响文章本身。
                </p>
            </form>
        </div>
    </div>
@endsection

@extends('layouts.admin')

@section('title', '撰写新文章')

@push('scripts')
    @include('admin.news.partials.tinymce')
@endpush

@section('content')
    <h1 class="text-xl font-bold mb-6">撰写新文章</h1>

    <form action="{{ route('admin.news.store') }}" method="post" class="bg-white rounded-lg border border-gray-200 p-6 space-y-5 max-w-4xl">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs text-gray-500 mb-1">所属分类 <span class="text-red-500">*</span></label>
                <select name="cid" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-white">
                    <option value="">请选择分类</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" {{ old('cid') == $class->id ? 'selected' : '' }}>
                            @if ($class->parent_id !== 0)　┗ @endif{{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">作者</label>
                <input type="text" name="author" value="{{ old('author', session('admin_name')) }}" maxlength="50"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">文章标题 <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="200"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">文章标签（可多选）</label>
            <select name="tags[]" multiple size="5"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-white">
                @forelse ($allTags as $tag)
                    <option value="{{ $tag->id }}" {{ in_array($tag->id, (array) old('tags', [])) ? 'selected' : '' }}>
                        {{ $tag->name }}
                    </option>
                @empty
                    <option value="" disabled>暂无可选标签，请先到「标签管理」创建</option>
                @endforelse
            </select>
            <p class="text-xs text-gray-400 mt-1">按住 Ctrl（Mac 为 Cmd）点击可多选；标签在「标签管理」中统一维护</p>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">SEO 关键词（可选）</label>
            <input type="text" name="keyword" value="{{ old('keyword') }}" maxlength="100" placeholder="仅输出到网页 meta keywords，供搜索引擎使用"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">摘要（列表页展示，可选）</label>
            <textarea name="summary" rows="3" maxlength="500" placeholder="留空时自动截取正文前 240 字"
                      class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">{{ old('summary') }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs text-gray-500 mb-1">发布状态</label>
                <select name="status" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-white">
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>发布（前台可见）</option>
                    <option value="0" {{ old('status', '1') == '0' ? 'selected' : '' }}>草稿（仅后台可见）</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">正文内容 <span class="text-red-500">*</span></label>
            <textarea id="content-editor" name="content" rows="16">{{ old('content') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                保存文章
            </button>
            <a href="{{ route('admin.news.index') }}" class="rounded-md border border-gray-300 px-5 py-2.5 text-sm hover:bg-gray-50">
                返回列表
            </a>
        </div>
    </form>
@endsection

@extends('layouts.admin')

@section('title', '分类管理')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold">分类管理</h1>
        <p class="text-xs text-gray-400">支持无限级分类，子分类按层级缩进显示</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-lg border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200 font-semibold text-sm">分类列表</div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left text-xs">
                    <tr>
                        <th class="px-5 py-3">名称</th>
                        <th class="px-5 py-3">层级</th>
                        <th class="px-5 py-3">文章数</th>
                        <th class="px-5 py-3 text-right">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($classes as $class)
                        <tr>
                            <td class="px-5 py-3">
                                <span style="padding-left: {{ ($class->path !== '0' ? substr_count($class->path, '-') + 1 : 0) * 16 }}px">
                                    @if ($class->parent_id !== 0)<span class="text-gray-400 mr-1">┗</span>@endif
                                    {{ $class->name }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-400 text-xs">
                                {{ $class->parent_id === 0 ? '顶级分类' : '子分类' }}
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $class->articles()->count() }}</td>
                            <td class="px-5 py-3 text-right">
                                <button type="button" data-rename="{{ $class->id }}" data-name="{{ $class->name }}"
                                        class="text-blue-600 hover:underline mr-3">重命名</button>
                                <form action="{{ route('admin.news-class.destroy', $class) }}" method="post" class="inline"
                                      onsubmit="return confirm('确定删除分类「{{ $class->name }}」吗？')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">删除</button>
                                </form>
                            </td>
                        </tr>
                        <tr id="rename-row-{{ $class->id }}" class="hidden bg-blue-50/50">
                            <td colspan="4" class="px-5 py-3">
                                <form action="{{ route('admin.news-class.update', $class) }}" method="post" class="flex gap-2 items-center">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $class->name }}" required maxlength="50"
                                           class="flex-1 max-w-xs rounded-md border border-gray-300 px-3 py-1.5 text-sm">
                                    <button type="submit" class="rounded-md bg-blue-600 px-3 py-1.5 text-white text-xs hover:bg-blue-700">保存</button>
                                    <button type="button" data-cancel-rename="{{ $class->id }}" class="text-gray-500 text-xs hover:underline">取消</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">暂无分类，请在右侧创建。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <h2 class="font-semibold text-sm mb-4">新建分类</h2>
                <form action="{{ route('admin.news-class.store') }}" method="post" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">分类名称</label>
                        <input type="text" name="name" required maxlength="50"
                               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">上级分类</label>
                        <select name="parent_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-white">
                            <option value="">作为顶级分类</option>
                            @foreach ($tops as $top)
                                <option value="{{ $top->id }}">{{ $top->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        创建分类
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-5 text-xs text-gray-500 leading-relaxed">
                <p class="font-semibold text-gray-700 mb-2">说明</p>
                <p>· 分类下存在子分类或文章时不可删除。</p>
                <p class="mt-1">· 列表页会展示分类下的子分类标签。</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-rename]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.dataset.rename;
                document.getElementById('rename-row-' + id).classList.remove('hidden');
            });
        });
        document.querySelectorAll('[data-cancel-rename]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.dataset.cancelRename;
                document.getElementById('rename-row-' + id).classList.add('hidden');
            });
        });
    </script>
@endpush

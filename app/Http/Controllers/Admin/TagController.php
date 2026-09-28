<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        return view('admin.tags.index', [
            'tags' => Tag::query()
                ->withCount('articles')
                ->orderByDesc('articles_count')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:tags,name'],
        ], [
            'name.required' => '请输入标签名称',
            'name.unique'   => '标签「:input」已存在',
        ]);

        Tag::query()->create($data);

        return back()->with('status', '标签「'.$data['name'].'」已创建');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:tags,name,'.$tag->id],
        ], [
            'name.required' => '标签名称不能为空',
            'name.unique'   => '标签「:input」已存在',
        ]);

        $tag->update($data);

        return back()->with('status', '标签已重命名为「'.$data['name'].'」');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->articles()->detach();
        $tag->delete();

        return back()->with('status', '标签「'.$tag->name.'」已删除（关联文章不受影响）');
    }
}

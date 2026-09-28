<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsClassController extends Controller
{
    public function index(): View
    {
        return view('admin.newsclass.index', [
            'classes' => NewsClass::query()->tree()->get(),
            'tops'    => NewsClass::query()->top()->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $parent = $data['parent_id'] ? NewsClass::query()->findOrFail($data['parent_id']) : null;

        NewsClass::query()->create($data + [
            'path' => NewsClass::resolvePath($parent),
            'sort_order' => 0,
        ]);

        return back()->with('status', '分类创建成功');
    }

    public function update(Request $request, NewsClass $news_class): RedirectResponse
    {
        $data = $this->validated($request);

        $news_class->update(['name' => $data['name']]);

        return back()->with('status', '分类已更新');
    }

    public function destroy(NewsClass $news_class): RedirectResponse
    {
        if ($news_class->children()->exists()) {
            return back()->withErrors(['error' => "分类「{$news_class->name}」下存在子分类，无法删除"]);
        }

        if ($news_class->articles()->exists()) {
            return back()->withErrors(['error' => "分类「{$news_class->name}」下存在文章，无法删除"]);
        }

        $news_class->delete();

        return back()->with('status', '分类已删除');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'      => ['required', 'string', 'max:50'],
            'parent_id' => ['nullable', 'integer', 'exists:newsclass,id'],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsClass;
use App\Models\NewsContent;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $articles = NewsContent::query()
            ->with('category')
            ->when($request->filled('q'), fn ($query) => $query->where('title', 'like', '%'.$request->query('q').'%'))
            ->when($request->filled('cid'), fn ($query) => $query->where('cid', (int) $request->query('cid')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.news.index', [
            'articles' => $articles,
            'classes'  => NewsClass::query()->tree()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.news.create', [
            'classes' => NewsClass::query()->tree()->get(),
            'allTags' => Tag::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $article = NewsContent::query()->create($data + [
            'date_time' => now(),
        ]);

        $this->syncTags($article, (array) $request->input('tags', []));

        $message = $data['status'] === 1 ? '文章发布成功' : '草稿已保存';

        return redirect()->route('admin.news.index')->with('status', $message);
    }

    public function edit(NewsContent $news): View
    {
        return view('admin.news.edit', [
            'article' => $news,
            'classes' => NewsClass::query()->tree()->get(),
            'allTags' => Tag::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, NewsContent $news): RedirectResponse
    {
        $news->update($this->validated($request));
        $this->syncTags($news, (array) $request->input('tags', []));

        return redirect()->route('admin.news.index')->with('status', '文章已更新');
    }

    public function destroy(NewsContent $news): RedirectResponse
    {
        $news->delete();

        return back()->with('status', '文章已删除');
    }

    /** 同步文章标签（勾选已有标签的 ID 列表，不影响 SEO 关键词 keyword 字段） */
    private function syncTags(NewsContent $article, array $tagIds): void
    {
        $article->tags()->sync($tagIds);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'cid'     => ['required', 'integer', 'exists:newsclass,id'],
            'title'   => ['required', 'string', 'max:200'],
            'author'  => ['nullable', 'string', 'max:50'],
            'keyword' => ['nullable', 'string', 'max:100'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'status'  => ['required', 'integer', 'in:0,1'],
            'tags'    => ['nullable', 'array'],
            'tags.*'  => ['integer', 'exists:tags,id'],
        ]);
    }
}

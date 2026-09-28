<?php

namespace App\Http\Controllers;

use App\Models\NewsClass;
use App\Models\NewsContent;
use App\Models\SiteConfig;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class FrontController extends Controller
{
    public function index(): View
    {
        $articles = NewsContent::query()
            ->published()
            ->with(['category', 'tags'])
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return $this->front('front.index', ['articles' => $articles]);
    }

    public function category(NewsClass $newsclass): View
    {
        $articles = NewsContent::query()
            ->published()
            ->with(['category', 'tags'])
            ->whereIn('cid', $newsclass->descendantIds())
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return $this->front('front.list', [
            'category'   => $newsclass,
            'subClasses' => $newsclass->children()->orderBy('id')->get(),
            'articles'   => $articles,
        ]);
    }

    public function show(NewsContent $newsContent): View
    {
        abort_unless($newsContent->status === 1, 404);

        $newsContent->increment('clicks');

        // 上一篇（更新的）/ 下一篇（更旧的）
        $prev = NewsContent::query()->published()->where('id', '>', $newsContent->id)->orderBy('id')->first(['id', 'title']);
        $next = NewsContent::query()->published()->where('id', '<', $newsContent->id)->latest()->first(['id', 'title']);

        $related = NewsContent::query()
            ->published()
            ->where('cid', $newsContent->cid)
            ->where('id', '!=', $newsContent->id)
            ->latest()
            ->limit(6)
            ->get(['id', 'title']);

        $newsContent->load('category');

        return $this->front('front.view', [
            'article' => $newsContent,
            'related' => $related,
            'prev'    => $prev,
            'next'    => $next,
        ]);
    }

    public function search(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $articles = NewsContent::query()
            ->published()
            ->with(['category', 'tags'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('content', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return $this->front('front.search', [
            'q'        => $q,
            'articles' => $articles,
        ]);
    }

    /** 标签聚合页：按标签名（tags 表）聚合已发布文章 */
    public function tag(string $tag): View
    {
        $tagModel = Tag::query()->where('name', trim($tag))->first();
        abort_unless($tagModel, 404);

        $articles = $tagModel->articles()
            ->published()
            ->with(['category', 'tags'])
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return $this->front('front.tag', [
            'tag'      => $tagModel->name,
            'articles' => $articles,
        ]);
    }

    /** 文章归档：按年-月分组的全部已发布文章 */
    public function archive(): View
    {
        $articles = NewsContent::query()
            ->published()
            ->orderByDesc('date_time')
            ->orderByDesc('id')
            ->get(['id', 'title', 'date_time']);

        $timeline = $articles
            ->groupBy(fn (NewsContent $a) => $a->date_time->format('Y'))
            ->map(fn (Collection $yearArticles, $year) => [
                'year'    => (int) $year,
                'count'   => $yearArticles->count(),
                'months'  => $yearArticles
                    ->groupBy(fn (NewsContent $a) => $a->date_time->format('m'))
                    ->map(fn (Collection $monthArticles, $month) => [
                        'month'    => (int) $month,
                        'articles' => $monthArticles,
                    ])
                    ->values(),
            ])
            ->values();

        return $this->front('front.archive', ['timeline' => $timeline]);
    }

    /** RSS 2.0 订阅源：最近 20 篇已发布文章（与主题无关） */
    public function feed(): Response
    {
        $articles = NewsContent::query()
            ->published()
            ->with('category')
            ->latest()
            ->limit(20)
            ->get();

        $site = SiteConfig::settings();
        $siteUrl = rtrim(config('app.url'), '/');

        $xml = view('front.feed', [
            'articles' => $articles,
            'site'     => $site,
            'siteUrl'  => $siteUrl,
            'builtAt'  => $articles->first()?->date_time ?? now(),
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=utf-8']);
    }

    /** sitemap.xml：首页 + 分类页 + 已发布文章（与主题无关） */
    public function sitemap(): Response
    {
        $siteUrl = rtrim(config('app.url'), '/');

        $articles = NewsContent::query()
            ->published()
            ->latest()
            ->get(['id', 'date_time']);

        $classes = NewsClass::query()->orderBy('id')->get();

        $xml = view('front.sitemap', [
            'siteUrl'  => $siteUrl,
            'articles' => $articles,
            'classes'  => $classes,
            'today'    => now()->toDateString(),
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /** 前台公共数据：导航分类 / 近期文章 / 归档 / 标签云 / 站点配置 */
    private function front(string $view, array $data = []): View
    {
        return view($view, $data + [
            'navClasses'     => NewsClass::query()->top()->orderBy('id')->get(),
            'recentArticles' => NewsContent::query()->published()->latest()->limit(6)->get(['id', 'title']),
            'archiveYears'   => $this->archiveYearCounts(),
            'tagCloud'       => $this->tagCloud(),
            'site'           => SiteConfig::settings(),
        ]);
    }

    /** 归档侧边栏数据：年份 => 文章数，倒序 */
    private function archiveYearCounts(): Collection
    {
        return NewsContent::query()
            ->published()
            ->selectRaw("DATE_FORMAT(date_time, '%Y') as y, COUNT(*) as total")
            ->groupBy('y')
            ->orderByDesc('y')
            ->get()
            ->map(fn ($row) => ['year' => (int) $row->y, 'count' => (int) $row->total]);
    }

    /** 标签云数据：按已发布文章数排序的标签，取前 20 */
    private function tagCloud(): Collection
    {
        return Tag::query()
            ->withCount(['articles' => fn ($query) => $query->published()])
            ->having('articles_count', '>', 0)
            ->orderByDesc('articles_count')
            ->limit(20)
            ->get()
            ->map(fn (Tag $tag) => ['name' => $tag->name, 'count' => $tag->articles_count]);
    }
}

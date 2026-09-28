@extends('layouts.front')

@section('title', $article->title.' - '.$site->get('site_name', 'XTCMS'))
@section('meta_description', $article->excerpt(120))
@section('meta_keywords', $article->keyword)

@section('content')
    <article class="bg-white rounded-lg border border-gray-200 p-6 sm:p-8">
        <h1 class="text-2xl font-bold mb-3 text-gray-900">{{ $article->title }}</h1>
        <p class="text-xs text-gray-500 border-b border-gray-100 pb-4 mb-6">
            发布时间：{{ $article->date_time->format('Y-m-d H:i') }}
            &nbsp;·&nbsp;作者：{{ $article->author ?: '佚名' }}
            &nbsp;·&nbsp;阅读：{{ $article->clicks + 1 }}
            @if ($article->category)
                &nbsp;·&nbsp;栏目：
                <a href="{{ route('front.category', $article->category) }}" class="hover:text-blue-600">{{ $article->category->name }}</a>
            @endif
        </p>
        @if ($article->tags->isNotEmpty())
            <div class="flex flex-wrap gap-1.5 mb-4 -mt-2">
                @foreach ($article->tags as $tag)
                    <a href="{{ route('front.tag', ['tag' => $tag->name]) }}"
                       class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 hover:bg-blue-50 hover:text-blue-600">#{{ $tag->name }}</a>
                @endforeach
            </div>
        @endif
        <div class="prose prose-sm sm:prose-base max-w-none break-words">
            {!! $article->content !!}
        </div>
    </article>

    @if ($prev || $next)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                @if ($prev)
                    <p class="text-xs text-gray-400 mb-1">上一篇</p>
                    <a href="{{ route('front.show', $prev) }}" class="text-gray-700 hover:text-blue-600">{{ $prev->shortTitle() }}</a>
                @endif
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4 sm:text-right">
                @if ($next)
                    <p class="text-xs text-gray-400 mb-1">下一篇</p>
                    <a href="{{ route('front.show', $next) }}" class="text-gray-700 hover:text-blue-600">{{ $next->shortTitle() }}</a>
                @endif
            </div>
        </div>
    @endif

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="font-semibold mb-3">相关文章</h3>
        <ul class="space-y-2 text-sm">
            @forelse ($related as $item)
                <li>
                    <a href="{{ route('front.show', $item) }}" class="text-gray-600 hover:text-blue-600">
                        {{ $item->shortTitle() }}
                    </a>
                </li>
            @empty
                <li class="text-gray-400">暂无相关文章</li>
            @endforelse
        </ul>
    </div>
@endsection

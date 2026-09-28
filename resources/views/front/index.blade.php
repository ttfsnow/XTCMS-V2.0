@extends('layouts.front')

@section('title', '博客主页')

@section('content')
    @forelse ($articles as $article)
        <article class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <h2 class="text-xl font-bold mb-2">
                <a href="{{ route('front.show', $article) }}" class="text-gray-900 hover:text-blue-600">
                    {{ $article->title }}
                </a>
            </h2>
            <p class="text-xs text-gray-500 mb-3">
                发布时间：{{ $article->date_time->format('Y-m-d') }}
                &nbsp;·&nbsp;栏目：
                @if ($article->category)
                    <a href="{{ route('front.category', $article->category) }}" class="hover:text-blue-600">{{ $article->category->name }}</a>
                @else
                    未分类
                @endif
                &nbsp;·&nbsp;阅读 {{ $article->clicks }}
            </p>
            @if ($article->tags->isNotEmpty())
                <div class="flex flex-wrap gap-1.5 mb-3">
                    @foreach ($article->tags as $tag)
                        <a href="{{ route('front.tag', ['tag' => $tag->name]) }}"
                           class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 hover:bg-blue-50 hover:text-blue-600">#{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif
            <p class="text-sm text-gray-600 leading-relaxed">{{ $article->excerpt() }}</p>
        </article>
    @empty
        <div class="bg-white rounded-lg border border-gray-200 p-10 text-center text-gray-400">
            暂无文章，请登录后台发布。
        </div>
    @endforelse

    {{ $articles->links() }}
@endsection

@extends('layouts.front')

@section('title', '标签：'.$tag)

@section('content')
    <div class="bg-white rounded-lg border border-gray-200 px-6 py-4">
        <h1 class="text-lg font-bold">
            标签「{{ $tag }}」（{{ $articles->total() }} 篇）
        </h1>
    </div>

    @forelse ($articles as $article)
        <article class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <h2 class="text-xl font-bold mb-2">
                <a href="{{ route('front.show', $article) }}" class="text-gray-900 hover:text-blue-600">
                    {{ $article->title }}
                </a>
            </h2>
            <p class="text-xs text-gray-500 mb-3">
                发布时间：{{ $article->date_time->format('Y-m-d') }}
                @if ($article->category)
                    &nbsp;·&nbsp;栏目：
                    <a href="{{ route('front.category', $article->category) }}" class="hover:text-blue-600">{{ $article->category->name }}</a>
                @endif
                &nbsp;·&nbsp;阅读 {{ $article->clicks }}
            </p>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $article->excerpt() }}</p>
        </article>
    @empty
        <div class="bg-white rounded-lg border border-gray-200 p-10 text-center text-gray-400">
            该标签下暂无文章。
        </div>
    @endforelse

    {{ $articles->links() }}
@endsection

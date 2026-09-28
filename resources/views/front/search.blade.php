@extends('layouts.front')

@section('title', '搜索：'.$q)

@section('content')
    <div class="bg-white rounded-lg border border-gray-200 px-6 py-4">
        <h1 class="text-lg font-bold">
            @if ($q !== '')
                搜索「{{ $q }}」的结果（{{ $articles->total() }} 条）
            @else
                请输入关键词进行搜索
            @endif
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
                    &nbsp;·&nbsp;栏目：{{ $article->category->name }}
                @endif
                &nbsp;·&nbsp;阅读 {{ $article->clicks }}
            </p>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $article->excerpt() }}</p>
        </article>
    @empty
        <div class="bg-white rounded-lg border border-gray-200 p-10 text-center text-gray-400">
            没有找到相关文章。
        </div>
    @endforelse

    {{ $articles->links() }}
@endsection

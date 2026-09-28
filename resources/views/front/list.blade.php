@extends('layouts.front')

@section('title', $category->name)

@section('content')
    <div class="bg-white rounded-lg border border-gray-200 px-6 py-4">
        <h1 class="text-lg font-bold">分类：{{ $category->name }}</h1>
        @if ($subClasses->isNotEmpty())
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($subClasses as $sub)
                    <a href="{{ route('front.category', $sub) }}"
                       class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-blue-50 hover:text-blue-600">
                        {{ $sub->name }}
                    </a>
                @endforeach
            </div>
        @endif
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
                &nbsp;·&nbsp;阅读 {{ $article->clicks }}
            </p>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $article->excerpt() }}</p>
        </article>
    @empty
        <div class="bg-white rounded-lg border border-gray-200 p-10 text-center text-gray-400">
            该分类下暂无文章。
        </div>
    @endforelse

    {{ $articles->links() }}
@endsection

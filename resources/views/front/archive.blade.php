@extends('layouts.front')

@section('title', '文章归档')

@section('content')
    <div class="bg-white rounded-lg border border-gray-200 px-6 py-4">
        <h1 class="text-lg font-bold">文章归档</h1>
        <p class="text-xs text-gray-500 mt-1">共 {{ $timeline->sum('count') }} 篇，按发布时间倒序</p>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        @forelse ($timeline as $yearGroup)
            <section id="year-{{ $yearGroup['year'] }}" class="mb-8 last:mb-0">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    {{ $yearGroup['year'] }} 年
                    <span class="text-sm font-normal text-gray-400">（{{ $yearGroup['count'] }} 篇）</span>
                </h2>
                @foreach ($yearGroup['months'] as $monthGroup)
                    <h3 class="text-sm font-semibold text-gray-500 mb-2">{{ $monthGroup['month'] }} 月</h3>
                    <ul class="space-y-2 mb-4">
                        @foreach ($monthGroup['articles'] as $article)
                            <li class="flex items-baseline gap-3 text-sm">
                                <span class="text-xs text-gray-400 shrink-0 w-14">{{ $article->date_time->format('m-d') }}</span>
                                <a href="{{ route('front.show', $article) }}" class="text-gray-700 hover:text-blue-600">
                                    {{ $article->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </section>
        @empty
            <p class="text-center text-gray-400 py-10">暂无文章。</p>
        @endforelse
    </div>
@endsection

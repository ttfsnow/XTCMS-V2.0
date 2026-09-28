{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ $site->get('site_name', 'XTCMS') }}</title>
        <link>{{ $siteUrl }}</link>
        <description>{{ $site->get('site_subtitle') }}</description>
        <language>zh-CN</language>
        <lastBuildDate>{{ $builtAt->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ $siteUrl }}/feed" rel="self" type="application/rss+xml" />
        @foreach ($articles as $article)
            <item>
                <title>{{ $article->title }}</title>
                <link>{{ $siteUrl }}/article/{{ $article->id }}</link>
                <guid isPermaLink="true">{{ $siteUrl }}/article/{{ $article->id }}</guid>
                <pubDate>{{ $article->date_time->toRfc2822String() }}</pubDate>
                @if ($article->category)
                    <category>{{ $article->category->name }}</category>
                @endif
                <description>{{ $article->excerpt(200) }}</description>
            </item>
        @endforeach
    </channel>
</rss>

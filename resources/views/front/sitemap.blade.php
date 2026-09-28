{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ $siteUrl }}</loc>
        <lastmod>{{ $today }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    @foreach ($classes as $class)
        <url>
            <loc>{{ $siteUrl }}/category/{{ $class->id }}</loc>
            <lastmod>{{ $today }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
    @foreach ($articles as $article)
        <url>
            <loc>{{ $siteUrl }}/article/{{ $article->id }}</loc>
            <lastmod>{{ $article->date_time->toDateString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach
</urlset>

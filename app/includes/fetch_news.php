<?php
// fetch_news.php

function getCachedNews($cacheFile = __DIR__ . '/news_cache.json', $cacheTime = 200) {
    // If cache file exists and is recent, return cached data
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        $data = file_get_contents($cacheFile);
        return json_decode($data, true);
    }

    // Otherwise, fetch fresh data from Google News RSS feed
    $rssUrl = 'https://news.google.com/rss/search?q=Technology+USA&hl=en-PH&gl=PH&ceid=PH:en';

    $rssContent = @file_get_contents($rssUrl);

    if (!$rssContent) return null;

    $xml = simplexml_load_string($rssContent);

    if (!$xml) return null;

    $headlines = [];
    // Get first 5 headlines
    $count = 0;
    foreach ($xml->channel->item as $item) {
        $headlines[] = [
            'title' => (string)$item->title,
            'link' => (string)$item->link,
            'pubDate' => (string)$item->pubDate,
        ];
        $count++;
        if ($count >= 5) break;
    }

    // Cache to file
    file_put_contents($cacheFile, json_encode($headlines));

    return $headlines;
}
<?php

/*
 *  ======================================================================
 *   FEED.PHP | MAINE BREAD OF LIFE
 *   AUTHOR: ARTHUR DANIEL BELANGER JR.
 *  ======================================================================
 */

header('Content-Type: application/rss+xml; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none';");

$base_url = 'https://mainebreadoflife.org';
$items = [
    [
        'title' => 'Family, Veteran and Emergency Shelters',
        'link' => '/shelters/',
        'description' => 'Bread of Life offers shelter options including family shelters and veteran housing.',
        'pubDate' => time()
    ],
    // Add more items here
];

echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>Maine Bread of Life - Augusta, Maine</title>
        <link><?= $base_url ?></link>
        <description>Community updates and services</description>
        <lastBuildDate><?= date(DATE_RSS) ?></lastBuildDate>
        <generator>Dynamic RSS Generator</generator>
        <?php foreach ($items as $item): ?>
        <item>
            <title><?= htmlspecialchars($item['title']) ?></title>
            <link><?= $base_url . $item['link'] ?></link>
            <guid><?= $base_url . $item['link'] ?></guid>
            <pubDate><?= date(DATE_RSS, $item['pubDate']) ?></pubDate>
            <dc:creator><![CDATA[Arthur Belanger]]></dc:creator>
            <description><![CDATA[<?= $item['description'] ?>]]></description>
        </item>
        <?php endforeach; ?>
    </channel>
</rss>
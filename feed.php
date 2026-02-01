<?php

/**
 * Optimized for GoDaddy hosting
 * Author: Arthur Belanger
 * Email: arthur.belanger@maine.edu
 * https://github.com/coding-with-arty/maineBreadOfLife
 * Date: 2025-06-04
 */

header('Content-Type: application/rss+xml; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none';");

$base_url = 'https://mainebreadoflife.org';
$items = [
    [
        'title' => 'Emergency Shelter Services',
        'link' => '/shelters/',
        'description' => 'Bread of Life provides emergency shelter services for individuals and families experiencing homelessness in the Augusta area. Our shelters offer a safe, supportive environment with access to meals, case management, and resources to help guests regain stability.',
        'pubDate' => strtotime('2025-06-01')
    ],
    [
        'title' => 'Soup Kitchen & Community Meals',
        'link' => '/soup-kitchen/',
        'description' => 'Our soup kitchen serves nutritious meals to anyone in need, no questions asked. Open 365 days a year, we provide breakfast, lunch, and dinner to combat food insecurity in our community.',
        'pubDate' => strtotime('2025-06-02')
    ],
    [
        'title' => 'The Arts House',
        'link' => '/arts-house/',
        'description' => 'A unique program that combines art therapy and life skills training to help individuals experiencing homelessness or housing insecurity. The Arts House provides a creative outlet and valuable skills development.',
        'pubDate' => strtotime('2025-06-03')
    ],
    [
        'title' => 'Veteran Support Services',
        'link' => '/services/',
        'description' => 'Specialized support for veterans experiencing homelessness, including housing assistance, case management, and connection to VA benefits and resources.',
        'pubDate' => strtotime('2025-06-04')
    ],
    [
        'title' => 'Volunteer Opportunities',
        'link' => '/volunteer/',
        'description' => 'Join our team of dedicated volunteers! Opportunities include meal service, shelter support, fundraising events, and skills-based volunteering. Make a difference in your community today.',
        'pubDate' => strtotime('2025-06-05')
    ],
    [
        'title' => 'Community Resources & Referrals',
        'link' => '/services/',
        'description' => 'Access to essential services including housing assistance, healthcare referrals, job training programs, and more. Our case managers work one-on-one with guests to connect them with the resources they need.',
        'pubDate' => strtotime('2025-06-06')
    ]
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
            <title><?= htmlspecialchars($item['title'], ENT_XML1, 'UTF-8') ?></title>
            <link><?= $base_url . $item['link'] ?></link>
            <guid><?= $base_url . $item['link'] ?></guid>
            <pubDate><?= date(DATE_RSS, $item['pubDate']) ?></pubDate>
            <dc:creator><![CDATA[Arthur Belanger]]></dc:creator>
            <description><![CDATA[<?= $item['description'] ?>]]></description>
        </item>
        <?php endforeach; ?>
    </channel>
</rss>
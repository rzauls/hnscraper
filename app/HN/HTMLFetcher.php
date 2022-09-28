<?php

namespace App\HN;

use App\Interfaces\HNClient;
use App\Models\Post;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

/*
 * HTMLFetcher is the html-scraper implementation for fetching HN data
 */

class HTMLFetcher implements HNClient
{
    public function fetch(): Collection
    {
        $client = new Client(HttpClient::create(['timeout' => env('FETCH_TIMEOUT', 10)]));
        $crawler = $client->request('GET', env('TARGET_URL', 'https://news.ycombinator.com/'));
        $crawler = $crawler->filter('.itemlist > tr');
        if (!$crawler->count()) {
            throw new \RuntimeException('filtered NodeList is empty, possibly HN html structure has changed or TARGET_URL is invalid');
        }
        $tableRows = $crawler->each(function (Crawler $node) {
            switch ($node) {
                case ($node->matches('.spacer') || $node->matches('.morespace')):
                    // ignore spacers, since they don't contain any relevant data
                    return null;
                case($node->matches('.athing') && $node->attr('id')):
                    // ignore AD posts (they don't have vote buttons)
                    if (!$node->filter('.votelinks')->count()) {
                        return null;
                    }
                    // parse title row
                    $id = (int)$node->attr('id');
                    $titleNode = $node->filter('.title');

                    $titleLink = $titleNode->filter('.titleline')->first();
                    if ($titleLink->count()) {
                        return [
                            'id' => $id,
                            'title' => $titleLink->innerText(),
                            'link' => $titleLink->filter('a')->first()->attr('href'),
                        ];
                    }
                default:
                    // parse subtitle row
                    $scoreNode = $node->filter('.score')->first();
                    if ($scoreNode->count()) {
                        return [
                            'id' => (int)substr($scoreNode->attr('id'), 6),
                            'points' => (int)substr($scoreNode->innerText(), 0, -7),
                            'author' => $node->filter('.hnuser')->first()->innerText(),
                            'created_at' => $node->filter('.age')->first()->attr('title')
                        ];
                    }
                    return null;

            }
        });

        // remap and merge rows by post ID
        $keyedPosts = [];
        foreach ($tableRows as $row) {
            // ignore null rows, since those are spacers and other garbage
            if ($row) {
                foreach ($row as $k => $v) {
                    $keyedPosts[$row['id']][$k] = $v;
                }
            }
        }


        $col = new Collection();
        array_map(function ($postData) use ($col) {
            $createdAt = Carbon::parse($postData['created_at']);
            $p = new Post(
                [
                    'id' => $postData['id'],
                    'title' => $postData['title'],
                    'author' => $postData['author'],
                    'points' => $postData['points'],
                    'link' => $postData['link'],
                    'created_at' => $createdAt,
                    'updated_at' => Carbon::now(),
                ]
            );
            $col->add($p);
        }, $keyedPosts);

        return $col;
    }
}

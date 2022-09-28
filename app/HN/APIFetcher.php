<?php

namespace App\HN;

use App\Interfaces\HNClient;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


class APIFetcher implements HNClient
{
    public function fetch(): Collection
    {
        $baseURL = env('TARGET_API_URL', 'https://hacker-news.firebaseio.com/v0/');

        $client = HttpClient::create(['timeout' => env('FETCH_TIMEOUT', 10)]);


        $col = new Collection();
        $res = $client->request('GET', $baseURL . 'topstories.json');
        $idList = json_decode($res->getContent());
        // fetch only first 30 entries (equivalent of fetching the first html page);
        foreach (array_slice($idList, 0, 30) as $id) {
            $res = json_decode($client->request('GET', $baseURL . 'item/' . $id . '.json')->getContent());
            if ($res->type !== 'story') {
                // skip non-story rows (job advertisements)
                continue;
            }
            $createdAt = Carbon::createFromTimestamp($res->time);
            // text posts do not contain an url, so we generate it by item id
            if (property_exists($res, 'url')) {
                $url = $res->url;
            } else {
                $url = env('TARGET_URL', 'https://news.ycombinator.com/') . "/item?id={$res->id}";
            }
            $p = new Post(
                [
                    'id' => $res->id,
                    'title' => $res->title,
                    'author' => $res->by,
                    'points' => $res->score,
                    'link' => $url,
                    'created_at' => $createdAt,
                    'updated_at' => Carbon::now(),
                ]
            );

            $col->add($p);
        }

        return $col;
    }
}

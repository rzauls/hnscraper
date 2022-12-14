<?php

namespace App\HN;

use App\Interfaces\HNClient;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpClient\HttpClient;


/*
 * APIFetcher is a firebase API based implementation for HNClient interface
 */
class APIFetcher implements HNClient
{
    public function GetPosts(): Collection
    {
        $baseURL = env('TARGET_API_URL', 'https://hacker-news.firebaseio.com/v0/');

        $client = HttpClient::create(['timeout' => env('FETCH_TIMEOUT', 10)]);


        $col = new Collection();
        $res = $client->request('GET', $baseURL . 'topstories.json');
        $idList = json_decode($res->getContent());
        Log::debug("retrieved id list for topstories.json");
        // fetch only first 30 entries (equivalent of fetching the first html page);
        // in practice the first 30 rows always contain an ad or a job post, so we get 29 "entries" parsed
        foreach (array_slice($idList, 0, 30) as $id) {
            Log::debug("fetching json data for {$id}");
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

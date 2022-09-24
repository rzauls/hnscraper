<?php

namespace App\Console\Commands;

use App\Models\Post;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Console\Command;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class FetchPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches HackerNews posts and saves the data to storage.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws RuntimeException // in case the HN structure has changed or the parsed an item list is empty
     * @return array
     */
    public function handle(): array
    {
        $client = new Client(HttpClient::create(['timeout' => env('FETCH_TIMEOUT', 10)]));
        $crawler = $client->request('GET', env('TARGET_URL','https://news.ycombinator.com/'));
        $crawler = $crawler->filter('.itemlist > tr');
        if (!$crawler->count()) {
            throw new RuntimeException('filtered NodeList is empty, possibly HN html structure has changed or TARGET_URL is invalid');
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
                    return [
                        'id' => $id,
                        'title' => $titleNode->filter('.titlelink')->first()->innerText(),
                        'link' => $titleNode->filter('.titlelink')->first()->attr('href'),
                    ];
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

        // persist data
        array_map(function ($postData) {
            $createdAt = Carbon::parse($postData['created_at']);
            Post::updateOrCreate(
                ['id' => $postData['id']],
                [
                    'title' => $postData['title'],
                    'author' => $postData['author'],
                    'points' => $postData['points'],
                    'link' => $postData['link'],
                    'created_at' => $createdAt,
                    'updated_at' => Carbon::now(),
                ]
            );
        }, $keyedPosts);

        dump(Post::all()->toArray());
        return $keyedPosts;
    }
}

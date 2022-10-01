<?php

namespace App\Console\Commands;

use App\HN\APIFetcher;
use App\HN\HTMLFetcher;
use App\Http\Controllers\FetchController;
use App\Models\Post;
use Carbon\Carbon;
use Goutte\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
    public function handle(): void
    {
        $hn = new FetchController();
        $source = env("HN_DATA_SOURCE", "html");
        switch ($source) {
            case 'api':
                $client = new APIFetcher();
                break;
            default:
                $client = new HTMLFetcher();
        }

        $posts = $hn->GetPosts($client);

        $posts->each(function (Post $p) {
            $postRecord = Post::withTrashed()->where('id', '=', $p->id)->first();
            if ($postRecord !== null) {
                // only update if not soft-deleted
                if (!$postRecord->trashed()) {
                    $postRecord->title = $p->title;
                    $postRecord->author = $p->author;
                    $postRecord->points = $p->points;
                    $postRecord->link = $p->link;
                    $postRecord->save();
                } else {
                    $this->info("skipping a trashed post with id " . $p->id, 'v');
                }
            } else {
                $p->save();
            }
        }
        );

        $successMsg = "successfully imported {$posts->count()} data points from '{$source}' data source";
        Log::info($successMsg);
        $this->info($successMsg, 'normal');
    }
}

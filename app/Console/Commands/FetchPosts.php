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
        switch (env("HN_DATA_SOURCE", "html")) {
            case 'api':
                // TODO: implement api fetcher
                $client = new APIFetcher();
                break;
            default:
                $client = new HTMLFetcher();
        }

        $posts = $hn->GetPosts($client);

        $posts->each(function (Post $p) {
            // TODO: check if post already imported and do not update if its soft-deleted
            $p->updateorCreate(['id' => $p->id], [
                'title' => $p->title,
                'author' => $p->author,
                'points' => $p->points,
                'link' => $p->link,
                'created_at' => $p->created_at,
                'updated_at' => $p->updated_at,
            ]);
        }
        );

        Log::info("successfully imported {$posts->count()} data points");
    }
}

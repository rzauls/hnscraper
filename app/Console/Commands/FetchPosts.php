<?php

namespace App\Console\Commands;

use App\HN\APIFetcher;
use App\HN\HTMLFetcher;
use App\Http\Controllers\FetchController;
use App\Interfaces\HNClient;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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


    protected $hnClient;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->hnClient = app(HNClient::class);
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $posts = $this->hnClient->GetPosts();

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

        $source = config('hn.default');
        $successMsg = "successfully imported {$posts->count()} data points from '{$source}' data source";
        Log::info($successMsg);
        $this->info($successMsg, 'normal');
        return 0;
    }
}

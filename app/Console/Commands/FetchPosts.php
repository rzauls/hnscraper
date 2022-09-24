<?php

namespace App\Console\Commands;

use Goutte\Client;
use Illuminate\Console\Command;
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
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
// TODO: move all this to some sort of callable controller
        $client = new Client(HttpClient::create(['timeout' => 10]));
        $crawler = $client->request('GET', 'https://news.ycombinator.com/'); // TODO: hardcode the link in ENV variables
        $tableRows = $crawler->filter('.itemlist > tr')->each(function (Crawler $node) {
            // ignore spacers, since they don't contain any relevant data
            if ($node->matches('.spacer') || $node->matches('.morespace')) {
                return null;
            }

            if ($node->matches('.athing') && $node->attr('id')) {
                // process title row
                $id = (int)$node->attr('id');
                $titleNode = $node->filter('.title');
                return [
                    'id' => $id,
                    'title' => $titleNode->filter('.titlelink')->first()->innerText(),
                    'link' => $titleNode->filter('.titlelink')->first()->attr('href'),
                ];
            } else {
                // process subtitle row
                $scoreNode = $node->filter('.score')->first();
                if ($scoreNode->count()) {
                    return [
                        'id'=>(int)substr($scoreNode->attr('id'), 6),
                        'score'=> (int)substr($scoreNode->innerText(), 0, -7),
                        'postedBy' => $node->filter('.hnuser')->first()->innerText(),
                        'createdAt' => $node->filter('.age')->first()->attr('title')
                    ];
                }
            }
        });

        $keyedPosts = [];
        foreach($tableRows as $row) {
            // ignore null rows, since those are spacers and other garbage
            if ($row) {
                // remap and merge rows by post ID
                foreach($row as $k => $v) {
                    $keyedPosts[$row['id']][$k] = $v;
                }
            }
        }

        dd($keyedPosts);

        // generate models


        // save the models


        // output new/changed model info
        return 0;
    }
}

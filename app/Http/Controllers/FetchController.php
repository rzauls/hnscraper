<?php

namespace App\Http\Controllers;

use App\Interfaces\HNClient;
use Illuminate\Database\Eloquent\Collection;

class FetchController extends Controller
{
    public function GetPosts(HNClient $client): Collection
    {
        return $client->fetch();
    }
}

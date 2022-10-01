<?php

namespace App\Http\Controllers;

use App\Interfaces\HNClient;
use Illuminate\Database\Eloquent\Collection;

class FetchController extends Controller
{
    public function GetPosts(HNClient $client): Collection
    {
        // TODO: check if post already imported and do not update if its soft-deleted
        return $client->fetch();
    }
}

<?php

namespace App\HN;

use App\Interfaces\HNClient;
use Illuminate\Database\Eloquent\Collection;


class APIFetcher implements HNClient
{
    public function fetch(): Collection
    {
        // TODO: implement
        $col = new Collection();

        return $col;
    }
}

<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface HNClient {
    public function fetch(): Collection;
}

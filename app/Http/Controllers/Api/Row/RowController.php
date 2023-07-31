<?php

namespace App\Http\Controllers\Api\Row;

use App\Http\Controllers\Controller;
use App\Models\Row;
use Illuminate\Http\Request;

class RowController extends Controller
{
    public function show (): \Illuminate\Database\Eloquent\Collection|array
    {
        return Row::query()->get()->groupBy('date');
    }

    public function clean()
    {
        return Row::query()->delete();
    }
}

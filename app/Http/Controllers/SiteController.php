<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\SiteMetric;
use Carbon\Carbon;

class SiteController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();
        $sites = Site::with(['metrics' => function ($query) use ($today) {
            $query->where('date', $today);
        }])->get();

        return view('sites.index', compact('sites'));
    }
}


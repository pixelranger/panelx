<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteMetric;
use Illuminate\Http\Request;

class SiteController extends Controller
{

    public function index()
    {
        $today = now()->toDateString(); // Текущая дата

        // Получаем сайты с их метриками за сегодня
        $sites = Site::with(['metrics' => function ($query) use ($today) {
            $query->where('date', $today);
        }])->get();

        return view('sites.index', compact('sites'));
    }
}


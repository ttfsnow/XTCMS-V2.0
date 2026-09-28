<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsClass;
use App\Models\NewsContent;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'articleCount'   => NewsContent::query()->count(),
            'categoryCount'  => NewsClass::query()->count(),
            'latestArticles' => NewsContent::query()->latest()->limit(5)->get(),
        ]);
    }
}

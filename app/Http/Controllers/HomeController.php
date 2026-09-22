<?php

namespace App\Http\Controllers;

use App\Services\HomepageService;

class HomeController extends Controller
{
    public function index(HomepageService $homepageService)
    {
        return view('welcome', $homepageService->data());
    }
}

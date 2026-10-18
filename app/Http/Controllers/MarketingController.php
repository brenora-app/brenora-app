<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class MarketingController extends Controller
{
    public function home(): View
    {
        return view('marketing.home');
    }

    public function product(): View
    {
        return view('marketing.product');
    }

    public function about(): View
    {
        return view('marketing.about');
    }

    public function pricing(): View
    {
        return view('marketing.pricing');
    }
}

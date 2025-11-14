<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the storefront home page.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        return view('store.home');
    }
}

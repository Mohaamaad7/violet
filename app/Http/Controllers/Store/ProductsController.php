<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display the products listing page.
     */
    public function index()
    {
        return view('store.products');
    }
}

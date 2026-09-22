<?php

namespace App\Http\Controllers;

use App\Services\ShippingFeeCalculator;

class StoreController extends Controller
{
    public function index()
    {
        return view('stores.index', [
            'branches' => ShippingFeeCalculator::branches(),
        ]);
    }
}

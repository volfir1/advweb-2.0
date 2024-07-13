<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function showDashboard()
    {
        return view('customer.pages.dashboard');
    }

    public function cart(){
        return view('customer.cart');
    }
}

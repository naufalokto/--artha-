<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display the inventory page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('inventory');
    }
} 
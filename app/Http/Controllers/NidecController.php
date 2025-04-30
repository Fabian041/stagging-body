<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NidecController extends Controller
{
    public function index()
    {
        return view('pages.nidec.index');
    }
}

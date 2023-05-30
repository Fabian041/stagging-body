<?php

namespace App\Http\Controllers;

use App\Imports\PartImport;
use Illuminate\Http\Request;
use App\Imports\ManifestImport;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index()
    {
        return view('pages.dashboard');
    }

    public function importPart(Request $request)
    {
        Excel::import(new PartImport, $request->file('file')->store('files'));

        return redirect()->back()->with('success', 'Part berhasil diupload!');
    }
    
    public function importManifest(Request $request)
    {
        Excel::import(new ManifestImport, $request->file('file')->store('files'));

        return redirect()->back()->with('success', 'Manifest berhasil diupload!');
    }
}

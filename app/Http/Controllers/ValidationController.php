<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KanbanPairing;

class ValidationController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.validation.index');
    }

    public function pair(Request $request)
    {
        $part = $request->query('part');

        if (!$part) {
            return response()->json([
                'status' => 'error',
                'message' => 'Part number is required',
            ], 400);
        }

        $pair = KanbanPairing::where('assembly_part', $part)
            ->orWhere('painting_part', $part)
            ->first();

        if (!$pair) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Part not found in pairings'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'type' => $part === $pair->assembly_part ? 'assembly' : 'painting',
            'assembly' => $pair->assembly_part,
            'painting' => $pair->painting_part,
            'qty_painting' => $pair->qty_painting,
            'qty_assy' => $pair->qty_assy
        ]);
    }
}

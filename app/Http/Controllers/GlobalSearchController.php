<?php

namespace App\Http\Controllers;

use App\Models\InternalPart;
use App\Models\LoadingList;
use App\Models\MasterAlamatChute;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * Pencarian data lintas modul untuk global search di navbar.
     * Scope saat ini: Loading List / PDS + Master Alamat Chute.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['query' => $q, 'results' => []]);
        }

        $like = '%' . $q . '%';
        $results = [];

        // Loading List / PDS
        LoadingList::query()
            ->with('customer:id,name')
            ->where(function ($w) use ($like) {
                $w->where('number', 'like', $like)
                    ->orWhere('pds_number', 'like', $like);
            })
            ->orderByDesc('created_at')
            ->limit(6)
            ->get(['id', 'number', 'pds_number', 'customer_id', 'cycle'])
            ->each(function ($ll) use (&$results) {
                $meta = array_filter([
                    $ll->pds_number,
                    optional($ll->customer)->name,
                    $ll->cycle ? 'Cycle ' . $ll->cycle : null,
                ]);

                $results[] = [
                    'type'     => 'Loading List',
                    'title'    => $ll->number,
                    'subtitle' => implode(' · ', $meta),
                    'url'      => url('/loading-list/' . $ll->id),
                    'icon'     => 'fa-truck-loading',
                ];
            });

        // Master Alamat Chute
        MasterAlamatChute::query()
            ->where(function ($w) use ($like) {
                $w->where('part_no_customer', 'like', $like)
                    ->orWhere('back_no', 'like', $like)
                    ->orWhere('part_name', 'like', $like);
            })
            ->orderBy('part_no_customer')
            ->limit(6)
            ->get(['id', 'part_no_customer', 'back_no', 'part_name', 'alamat_chute'])
            ->each(function ($m) use (&$results) {
                $title = $m->part_no_customer . ($m->back_no !== '' ? ' · ' . $m->back_no : '');
                $sub = array_filter([
                    $m->part_name !== '' ? $m->part_name : null,
                    $m->alamat_chute ? '→ ' . $m->alamat_chute : null,
                ]);

                $results[] = [
                    'type'     => 'Alamat Chute',
                    'title'    => $title,
                    'subtitle' => implode(' ', $sub),
                    'url'      => route('pulling.master-alamat-chute'),
                    'icon'     => 'fa-map-marker-alt',
                ];
            });

        // Part → Production Result & Production Stock
        InternalPart::query()
            ->where(function ($w) use ($like) {
                $w->where('part_number', 'like', $like)
                    ->orWhere('back_number', 'like', $like)
                    ->orWhere('part_name', 'like', $like);
            })
            ->orderBy('back_number')
            ->limit(5)
            ->get(['id', 'part_number', 'back_number', 'part_name'])
            ->each(function ($p) use (&$results) {
                $backNo = trim((string) $p->back_number);
                $title = $backNo !== '' ? $backNo : $p->part_number;
                $subtitle = implode(' · ', array_filter([
                    $p->part_name ?: null,
                    $p->part_number ?: null,
                ]));

                // Production Result butuh back number (halaman di-key oleh back number)
                if ($backNo !== '') {
                    $results[] = [
                        'type'     => 'Production Result',
                        'title'    => $title,
                        'subtitle' => $subtitle,
                        'url'      => route('dashboard.prodResult', ['back_number' => $backNo]),
                        'icon'     => 'fa-chart-line',
                    ];
                }

                $results[] = [
                    'type'     => 'Production Stock',
                    'title'    => $title,
                    'subtitle' => $subtitle,
                    'url'      => route('dashboard.productionStock', $backNo !== '' ? ['back_number' => $backNo] : []),
                    'icon'     => 'fa-box',
                ];
            });

        return response()->json(['query' => $q, 'results' => $results]);
    }
}

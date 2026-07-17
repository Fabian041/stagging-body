<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use App\Models\Line;
use App\Models\Mold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    public function index()
    {
        $stats = [
            'molds' => Mold::count(),
            'internalParts' => InternalPart::count(),
            'customerParts' => CustomerPart::count(),
        ];

        return view('pages.master.index', compact('stats'));
    }

    public function molds()
    {
        $molds = Mold::with(['moldHasParts.internalPart.line'])->latest()->get();
        $internalParts = InternalPart::with('line')->orderBy('part_number')->get();

        return view('pages.master.molds', compact('molds', 'internalParts'));
    }

    public function storeMold(Request $request)
    {
        $request->validate([
            'tag' => ['required', 'string', 'max:100', 'unique:molds,tag'],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
            'internal_part_ids' => ['nullable', 'array'],
            'internal_part_ids.*' => ['integer', 'exists:internal_parts,id'],
        ]);

        DB::transaction(function () use ($request) {
            $mold = Mold::create([
                'tag' => strtoupper(trim($request->tag)),
                'name' => trim($request->name),
                'is_active' => $request->boolean('is_active', true),
            ]);

            foreach ($request->input('internal_part_ids', []) as $internalPartId) {
                $mold->moldHasParts()->create([
                    'internal_part_id' => $internalPartId,
                ]);
            }
        });

        return redirect()->route('dashboard.master.molds.index')->with('success', 'Mold baru berhasil disimpan.');
    }

    public function updateMold(Request $request, Mold $mold)
    {
        $request->validate([
            'tag' => ['required', 'string', 'max:100', 'unique:molds,tag,' . $mold->id],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
            'internal_part_ids' => ['nullable', 'array'],
            'internal_part_ids.*' => ['integer', 'exists:internal_parts,id'],
        ]);

        DB::transaction(function () use ($request, $mold) {
            $mold->update([
                'tag' => strtoupper(trim($request->tag)),
                'name' => trim($request->name),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $mold->moldHasParts()->delete();

            foreach ($request->input('internal_part_ids', []) as $internalPartId) {
                $mold->moldHasParts()->create([
                    'internal_part_id' => $internalPartId,
                ]);
            }
        });

        return redirect()->route('dashboard.master.molds.index')->with('success', 'Mold berhasil diperbarui.');
    }

    public function destroyMold(Mold $mold)
    {
        DB::transaction(function () use ($mold) {
            $mold->moldHasParts()->delete();
            $mold->delete();
        });

        return redirect()->route('dashboard.master.molds.index')->with('success', 'Mold berhasil dihapus.');
    }

    public function internalParts()
    {
        $internalParts = InternalPart::with('line')->latest()->get();
        $lines = Line::orderBy('name')->get();

        return view('pages.master.internal-parts', compact('internalParts', 'lines'));
    }

    public function storeInternalPart(Request $request)
    {
        $request->validate([
            'line_id' => ['nullable', 'integer', 'exists:lines,id'],
            'part_number' => ['required', 'string', 'max:100'],
            'back_number' => ['nullable', 'string', 'max:100'],
            'part_name' => ['nullable', 'string', 'max:150'],
        ]);

        InternalPart::create([
            'line_id' => $request->line_id,
            'part_number' => trim($request->part_number),
            'back_number' => trim($request->back_number),
            'part_name' => trim($request->part_name),
        ]);

        return redirect()->route('dashboard.master.parts.index')->with('success', 'Master Part berhasil ditambah.');
    }

    public function updateInternalPart(Request $request, InternalPart $internalPart)
    {
        $request->validate([
            'line_id' => ['nullable', 'integer', 'exists:lines,id'],
            'part_number' => ['required', 'string', 'max:100'],
            'back_number' => ['nullable', 'string', 'max:100'],
            'part_name' => ['nullable', 'string', 'max:150'],
        ]);

        $internalPart->update([
            'line_id' => $request->line_id,
            'part_number' => trim($request->part_number),
            'back_number' => trim($request->back_number),
            'part_name' => trim($request->part_name),
        ]);

        return redirect()->route('dashboard.master.parts.index')->with('success', 'Master Part berhasil diperbarui.');
    }

    public function destroyInternalPart(InternalPart $internalPart)
    {
        DB::transaction(function () use ($internalPart) {
            $internalPart->customerPart()->delete();
            $internalPart->delete();
        });

        return redirect()->route('dashboard.master.parts.index')->with('success', 'Master Part berhasil dihapus.');
    }

    public function customerParts()
    {
        $customerParts = CustomerPart::with(['internalPart.line', 'customer'])->latest()->get();
        $internalParts = InternalPart::orderBy('part_number')->get();
        $customers = Customer::orderBy('name')->get();

        return view('pages.master.customer-parts', compact('customerParts', 'internalParts', 'customers'));
    }

    public function storeCustomerPart(Request $request)
    {
        $request->validate([
            'internal_part_id' => ['required', 'integer', 'exists:internal_parts,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'part_number' => ['required', 'string', 'max:100'],
            'back_number' => ['nullable', 'string', 'max:100'],
            'qty_per_kanban' => ['nullable', 'integer', 'min:0'],
        ]);

        CustomerPart::create([
            'internal_part_id' => $request->internal_part_id,
            'customer_id' => $request->customer_id,
            'part_number' => trim($request->part_number),
            'back_number' => trim($request->back_number),
            'qty_per_kanban' => $request->qty_per_kanban,
        ]);

        return redirect()->route('dashboard.master.customer-parts.index')->with('success', 'Master Part Customer berhasil ditambah.');
    }

    public function updateCustomerPart(Request $request, CustomerPart $customerPart)
    {
        $request->validate([
            'internal_part_id' => ['required', 'integer', 'exists:internal_parts,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'part_number' => ['required', 'string', 'max:100'],
            'back_number' => ['nullable', 'string', 'max:100'],
            'qty_per_kanban' => ['nullable', 'integer', 'min:0'],
        ]);

        $customerPart->update([
            'internal_part_id' => $request->internal_part_id,
            'customer_id' => $request->customer_id,
            'part_number' => trim($request->part_number),
            'back_number' => trim($request->back_number),
            'qty_per_kanban' => $request->qty_per_kanban,
        ]);

        return redirect()->route('dashboard.master.customer-parts.index')->with('success', 'Master Part Customer berhasil diperbarui.');
    }

    public function destroyCustomerPart(CustomerPart $customerPart)
    {
        $customerPart->delete();

        return redirect()->route('dashboard.master.customer-parts.index')->with('success', 'Master Part Customer berhasil dihapus.');
    }
}

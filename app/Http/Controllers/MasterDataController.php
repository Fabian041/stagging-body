<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use App\Models\Line;
use App\Models\Mold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

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

    public function molds(Request $request)
    {
        $editingMold = $request->filled('edit')
            ? Mold::with('moldHasParts')->find($request->integer('edit'))
            : null;
        $moldCount = Mold::count();
        $internalParts = InternalPart::with('line')->orderBy('part_number')->get();
        $editingMoldPhotoUrl = $editingMold?->photo_url;

        return view('pages.master.molds', compact(
            'editingMold',
            'editingMoldPhotoUrl',
            'moldCount',
            'internalParts'
        ));
    }

    public function moldsData()
    {
        $query = Mold::query()->with(['moldHasParts.internalPart:id,back_number']);

        return DataTables::of($query)
            ->editColumn('tag', function (Mold $mold) {
                return '<strong>' . e($mold->tag) . '</strong>';
            })
            ->addColumn('parts', function (Mold $mold) {
                if ($mold->moldHasParts->isEmpty()) {
                    return '<span class="text-muted">Belum ada part</span>';
                }

                return $mold->moldHasParts->map(function ($relation) {
                    $backNumber = $relation->internalPart?->back_number ?? '-';

                    return '<span class="badge bg-secondary-subtle text-secondary mr-1 mb-1">'
                        . e($backNumber)
                        . '</span>';
                })->implode('');
            })
            ->editColumn('is_active', function (Mold $mold) {
                return $mold->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Nonaktif</span>';
            })
            ->addColumn('action', function (Mold $mold) {
                return view('pages.master.partials.mold-actions', compact('mold'))->render();
            })
            ->rawColumns(['tag', 'parts', 'is_active', 'action'])
            ->make(true);
    }

    public function storeMold(Request $request)
    {
        $request->merge([
            'tag' => strtoupper(trim((string) $request->input('tag'))),
            'name' => trim((string) $request->input('name')),
        ]);

        $request->validate([
            'tag' => ['required', 'string', 'max:100', 'unique:molds,tag'],
            'name' => ['required', 'string', 'max:150'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
            'internal_part_ids' => ['nullable', 'array'],
            'internal_part_ids.*' => ['integer', 'distinct', 'exists:internal_parts,id'],
        ]);

        $photo = $request->hasFile('photo')
            ? $this->storeMoldPhoto($request->file('photo'), $request->tag)
            : null;

        DB::transaction(function () use ($request, $photo) {
            $mold = Mold::create([
                'tag' => $request->tag,
                'name' => $request->name,
                'photo' => $photo,
                'is_active' => $request->boolean('is_active'),
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
        $request->merge([
            'tag' => strtoupper(trim((string) $request->input('tag'))),
            'name' => trim((string) $request->input('name')),
        ]);

        $request->validate([
            'tag' => ['required', 'string', 'max:100', Rule::unique('molds', 'tag')->ignore($mold->id)],
            'name' => ['required', 'string', 'max:150'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
            'internal_part_ids' => ['nullable', 'array'],
            'internal_part_ids.*' => ['integer', 'distinct', 'exists:internal_parts,id'],
        ]);

        $oldPhoto = $mold->photo;
        $newPhoto = $request->hasFile('photo')
            ? $this->storeMoldPhoto($request->file('photo'), $request->tag)
            : null;

        DB::transaction(function () use ($request, $mold, $newPhoto) {
            $values = [
                'tag' => $request->tag,
                'name' => $request->name,
                'is_active' => $request->boolean('is_active'),
            ];

            if ($newPhoto) {
                $values['photo'] = $newPhoto;
            }

            $mold->update($values);

            $mold->moldHasParts()->delete();

            foreach ($request->input('internal_part_ids', []) as $internalPartId) {
                $mold->moldHasParts()->create([
                    'internal_part_id' => $internalPartId,
                ]);
            }
        });

        if ($newPhoto && $oldPhoto && $oldPhoto !== $newPhoto) {
            $this->deleteMoldPhoto($oldPhoto);
        }

        return redirect()->route('dashboard.master.molds.index')->with('success', 'Mold berhasil diperbarui.');
    }

    public function destroyMold(Mold $mold)
    {
        $photo = $mold->photo;

        DB::transaction(function () use ($mold) {
            $mold->moldHasParts()->delete();
            $mold->delete();
        });

        $this->deleteMoldPhoto($photo);

        return redirect()->route('dashboard.master.molds.index')->with('success', 'Mold berhasil dihapus.');
    }

    public function internalParts(Request $request)
    {
        $editingPart = $request->filled('edit')
            ? InternalPart::find($request->integer('edit'))
            : null;
        $internalPartCount = InternalPart::count();
        $lines = Line::orderBy('name')->get();
        $editingPhotoUrl = null;

        if ($editingPart?->photo) {
            $filename = basename((string) $editingPart->photo);
            if (File::isFile(public_path('assets/img/pis/' . $filename))) {
                $editingPhotoUrl = asset('assets/img/pis/' . rawurlencode($filename));
            }
        }

        return view('pages.master.internal-parts', compact(
            'editingPart',
            'internalPartCount',
            'lines',
            'editingPhotoUrl'
        ));
    }

    public function internalPartsData(Request $request)
    {
        $query = InternalPart::query()
            ->leftJoin('lines', 'lines.id', '=', 'internal_parts.line_id')
            ->select('internal_parts.*', 'lines.name as line_name');

        if ($request->filled('line_id')) {
            $query->where('internal_parts.line_id', $request->integer('line_id'));
        }

        if ($request->boolean('missing_photo')) {
            $photoDirectory = public_path('assets/img/pis');
            $availablePhotos = File::isDirectory($photoDirectory)
                ? collect(File::files($photoDirectory))->map->getFilename()->values()->all()
                : [];

            if ($availablePhotos) {
                $query->where(function ($builder) use ($availablePhotos) {
                    $builder->whereNull('internal_parts.photo')
                        ->orWhere('internal_parts.photo', '')
                        ->orWhereNotIn('internal_parts.photo', $availablePhotos);
                });
            }
        }

        return DataTables::of($query)
            ->editColumn('line_name', function (InternalPart $part) {
                return $part->line_name ?? '-';
            })
            ->editColumn('back_number', function (InternalPart $part) {
                return $part->back_number ?? '-';
            })
            ->editColumn('part_name', function (InternalPart $part) {
                return $part->part_name ?? '-';
            })
            ->addColumn('photo_preview', function (InternalPart $part) {
                $filename = basename((string) $part->photo);
                $path = $filename !== '' ? public_path('assets/img/pis/' . $filename) : null;

                if (! $path || ! File::isFile($path)) {
                    return '<span class="master-photo-empty" title="Foto belum tersedia"><i class="fas fa-image"></i></span>';
                }

                $url = asset('assets/img/pis/' . rawurlencode($filename));

                return '<a class="master-photo-thumb" href="' . e($url) . '" target="_blank" rel="noopener" title="Lihat foto">'
                    . '<img src="' . e($url) . '" alt="Foto ' . e($part->back_number ?? $part->part_number) . '">'
                    . '</a>';
            })
            ->addColumn('action', function (InternalPart $part) {
                return view('pages.master.partials.internal-part-actions', compact('part'))->render();
            })
            ->rawColumns(['photo_preview', 'action'])
            ->make(true);
    }

    public function storeInternalPart(Request $request)
    {
        $this->normalizePartRequest($request);

        $request->validate([
            'line_id' => ['nullable', 'integer', 'exists:lines,id'],
            'part_number' => ['required', 'string', 'max:100', 'unique:internal_parts,part_number'],
            'back_number' => ['nullable', 'string', 'max:100'],
            'part_name' => ['nullable', 'string', 'max:150'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $photo = $request->hasFile('photo')
            ? $this->storeInternalPartPhoto($request->file('photo'), $request->back_number, $request->part_number)
            : ($request->back_number ? $request->back_number . '.jpeg' : null);

        InternalPart::create([
            'line_id' => $request->line_id,
            'part_number' => $request->part_number,
            'back_number' => $request->back_number,
            'part_name' => $request->part_name,
            'photo' => $photo,
        ]);

        return redirect()->route('dashboard.master.parts.index')->with('success', 'Master Part berhasil ditambah.');
    }

    public function updateInternalPart(Request $request, InternalPart $internalPart)
    {
        $this->normalizePartRequest($request);

        $request->validate([
            'line_id' => ['nullable', 'integer', 'exists:lines,id'],
            'part_number' => ['required', 'string', 'max:100', Rule::unique('internal_parts', 'part_number')->ignore($internalPart->id)],
            'back_number' => ['nullable', 'string', 'max:100'],
            'part_name' => ['nullable', 'string', 'max:150'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $values = [
            'line_id' => $request->line_id,
            'part_number' => $request->part_number,
            'back_number' => $request->back_number,
            'part_name' => $request->part_name,
        ];

        if ($request->hasFile('photo')) {
            $values['photo'] = $this->storeInternalPartPhoto(
                $request->file('photo'),
                $request->back_number,
                $request->part_number
            );
        }

        $internalPart->update($values);

        return redirect()->route('dashboard.master.parts.index')->with('success', 'Master Part berhasil diperbarui.');
    }

    public function destroyInternalPart(InternalPart $internalPart)
    {
        DB::transaction(function () use ($internalPart) {
            $internalPart->moldHasParts()->delete();
            $internalPart->customerParts()->delete();
            $internalPart->delete();
        });

        return redirect()->route('dashboard.master.parts.index')->with('success', 'Master Part berhasil dihapus.');
    }

    public function customerParts(Request $request)
    {
        $editingCustomerPart = $request->filled('edit')
            ? CustomerPart::find($request->integer('edit'))
            : null;
        $customerPartCount = CustomerPart::count();
        $internalParts = InternalPart::orderBy('part_number')->get();
        $customers = Customer::orderBy('name')->get();

        return view('pages.master.customer-parts', compact(
            'editingCustomerPart',
            'customerPartCount',
            'internalParts',
            'customers'
        ));
    }

    public function customerPartsData(Request $request)
    {
        $query = CustomerPart::query()
            ->leftJoin('internal_parts', 'internal_parts.id', '=', 'customer_parts.internal_part_id')
            ->leftJoin('customers', 'customers.id', '=', 'customer_parts.customer_id')
            ->select(
                'customer_parts.*',
                'internal_parts.part_number as internal_part_number',
                'customers.name as customer_name'
            );

        if ($request->filled('customer_id')) {
            $query->where('customer_parts.customer_id', $request->integer('customer_id'));
        }

        return DataTables::of($query)
            ->editColumn('customer_name', function (CustomerPart $customerPart) {
                return $customerPart->customer_name ?? '-';
            })
            ->editColumn('internal_part_number', function (CustomerPart $customerPart) {
                return $customerPart->internal_part_number ?? '-';
            })
            ->editColumn('back_number', function (CustomerPart $customerPart) {
                return $customerPart->back_number ?? '-';
            })
            ->editColumn('qty_per_kanban', function (CustomerPart $customerPart) {
                return $customerPart->qty_per_kanban ?? '-';
            })
            ->addColumn('action', function (CustomerPart $customerPart) {
                return view('pages.master.partials.customer-part-actions', compact('customerPart'))->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeCustomerPart(Request $request)
    {
        $this->normalizeCustomerPartRequest($request);

        $request->validate([
            'internal_part_id' => ['required', 'integer', 'exists:internal_parts,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'part_number' => ['required', 'string', 'max:100', 'unique:customer_parts,part_number'],
            'back_number' => ['nullable', 'string', 'max:100'],
            'qty_per_kanban' => ['required', 'integer', 'min:1'],
        ]);

        CustomerPart::create([
            'internal_part_id' => $request->internal_part_id,
            'customer_id' => $request->customer_id,
            'part_number' => $request->part_number,
            'back_number' => $request->back_number,
            'qty_per_kanban' => $request->qty_per_kanban,
        ]);

        return redirect()->route('dashboard.master.customer-parts.index')->with('success', 'Master Part Customer berhasil ditambah.');
    }

    public function updateCustomerPart(Request $request, CustomerPart $customerPart)
    {
        $this->normalizeCustomerPartRequest($request);

        $request->validate([
            'internal_part_id' => ['required', 'integer', 'exists:internal_parts,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'part_number' => ['required', 'string', 'max:100', Rule::unique('customer_parts', 'part_number')->ignore($customerPart->id)],
            'back_number' => ['nullable', 'string', 'max:100'],
            'qty_per_kanban' => ['required', 'integer', 'min:1'],
        ]);

        $customerPart->update([
            'internal_part_id' => $request->internal_part_id,
            'customer_id' => $request->customer_id,
            'part_number' => $request->part_number,
            'back_number' => $request->back_number,
            'qty_per_kanban' => $request->qty_per_kanban,
        ]);

        return redirect()->route('dashboard.master.customer-parts.index')->with('success', 'Master Part Customer berhasil diperbarui.');
    }

    public function destroyCustomerPart(CustomerPart $customerPart)
    {
        $customerPart->delete();

        return redirect()->route('dashboard.master.customer-parts.index')->with('success', 'Master Part Customer berhasil dihapus.');
    }

    private function normalizePartRequest(Request $request): void
    {
        $request->merge([
            'part_number' => strtoupper(trim((string) $request->input('part_number'))),
            'back_number' => $this->nullableTrimmedValue($request->input('back_number'), true),
            'part_name' => $this->nullableTrimmedValue($request->input('part_name')),
        ]);
    }

    private function normalizeCustomerPartRequest(Request $request): void
    {
        $request->merge([
            'part_number' => strtoupper(trim((string) $request->input('part_number'))),
            'back_number' => $this->nullableTrimmedValue($request->input('back_number'), true),
        ]);
    }

    private function storeInternalPartPhoto($photo, ?string $backNumber, string $partNumber): string
    {
        $baseName = trim((string) ($backNumber ?: $partNumber));
        $baseName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $baseName) ?: 'part';
        $filename = $baseName . '.' . strtolower($photo->extension());
        $directory = public_path('assets/img/pis');

        File::ensureDirectoryExists($directory);
        $photo->move($directory, $filename);

        return $filename;
    }

    private function storeMoldPhoto($photo, string $tag): string
    {
        $safeTag = preg_replace('/[^A-Za-z0-9._-]+/', '_', trim($tag)) ?: 'mold';
        $filename = $safeTag . '-' . Str::uuid() . '.' . strtolower($photo->extension());
        $directory = public_path('assets/img/molds');

        File::ensureDirectoryExists($directory);
        $photo->move($directory, $filename);

        return $filename;
    }

    private function deleteMoldPhoto(?string $photo): void
    {
        $filename = basename((string) $photo);

        if ($filename !== '') {
            File::delete(public_path('assets/img/molds/' . $filename));
        }
    }

    private function nullableTrimmedValue($value, bool $uppercase = false): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return $uppercase ? strtoupper($value) : $value;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPart;
use App\Models\InternalPart;
use App\Models\KanbanPairing;
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
            'lines' => Line::count(),
            'molds' => Mold::count(),
            'internalParts' => InternalPart::count(),
            'customerParts' => CustomerPart::count(),
        ];

        return view('pages.master.index', compact('stats'));
    }

    public function lines(Request $request)
    {
        $editingLine = $request->filled('edit')
            ? Line::find($request->integer('edit'))
            : null;
        $lineCount = Line::count();

        return view('pages.master.lines', compact('editingLine', 'lineCount'));
    }

    public function linesData()
    {
        $query = Line::query()
            ->withCount([
                'internalPart as internal_parts_count',
                'machines',
            ]);

        return DataTables::of($query)
            ->editColumn('name', fn (Line $line) => '<strong>'.e($line->name).'</strong>')
            ->editColumn('is_active', function (Line $line) {
                return $line->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Nonaktif</span>';
            })
            ->addColumn('action', function (Line $line) {
                return view('pages.master.partials.line-actions', compact('line'))->render();
            })
            ->rawColumns(['name', 'is_active', 'action'])
            ->make(true);
    }

    public function storeLine(Request $request)
    {
        $this->normalizeLineRequest($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[A-Z0-9._-]+$/', 'unique:lines,name'],
            'is_active' => ['required', 'boolean'],
        ]);

        Line::create($validated);

        return redirect()->route('dashboard.master.lines.index')
            ->with('success', 'Master Line berhasil ditambahkan.');
    }

    public function updateLine(Request $request, Line $line)
    {
        $this->normalizeLineRequest($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Z0-9._-]+$/',
                Rule::unique('lines', 'name')->ignore($line->id),
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        $line->update($validated);

        return redirect()->route('dashboard.master.lines.index')
            ->with('success', 'Master Line berhasil diperbarui.');
    }

    public function toggleLine(Line $line)
    {
        $line->update(['is_active' => ! $line->is_active]);

        return redirect()->route('dashboard.master.lines.index')
            ->with('success', 'Status Master Line berhasil diubah.');
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
                return '<strong>'.e($mold->tag).'</strong>';
            })
            ->addColumn('parts', function (Mold $mold) {
                if ($mold->moldHasParts->isEmpty()) {
                    return '<span class="text-muted">Belum ada part</span>';
                }

                return $mold->moldHasParts->map(function ($relation) {
                    $backNumber = $relation->internalPart?->back_number ?? '-';

                    return '<span class="badge bg-secondary-subtle text-secondary mr-1 mb-1">'
                        .e($backNumber)
                        .'</span>';
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
            if (File::isFile(public_path('assets/img/pis/'.$filename))) {
                $editingPhotoUrl = asset('assets/img/pis/'.rawurlencode($filename));
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
                $path = $filename !== '' ? public_path('assets/img/pis/'.$filename) : null;

                if (! $path || ! File::isFile($path)) {
                    return '<span class="master-photo-empty" title="Foto belum tersedia"><i class="fas fa-image"></i></span>';
                }

                $url = asset('assets/img/pis/'.rawurlencode($filename));

                return '<a class="master-photo-thumb" href="'.e($url).'" target="_blank" rel="noopener" title="Lihat foto">'
                    .'<img src="'.e($url).'" alt="Foto '.e($part->back_number ?? $part->part_number).'">'
                    .'</a>';
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
            'line_id' => [
                'nullable',
                'integer',
                Rule::exists('lines', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'part_number' => ['required', 'string', 'max:100', 'unique:internal_parts,part_number'],
            'back_number' => ['nullable', 'string', 'max:100'],
            'part_name' => ['nullable', 'string', 'max:150'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $photo = $request->hasFile('photo')
            ? $this->storeInternalPartPhoto($request->file('photo'), $request->back_number, $request->part_number)
            : ($request->back_number ? $request->back_number.'.jpeg' : null);

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
            'line_id' => [
                'nullable',
                'integer',
                Rule::exists('lines', 'id')->where(function ($query) use ($internalPart) {
                    $query->where(function ($lineQuery) use ($internalPart) {
                        $lineQuery->where('is_active', true);

                        if ($internalPart->line_id) {
                            $lineQuery->orWhere('id', $internalPart->line_id);
                        }
                    });
                }),
            ],
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

    public function kanbanPairings(Request $request)
    {
        $editingPairing = $request->filled('edit')
            ? KanbanPairing::find($request->integer('edit'))
            : null;
        $pairingCount = KanbanPairing::count();
        $projects = KanbanPairing::query()
            ->select('project')
            ->distinct()
            ->orderBy('project')
            ->pluck('project');

        return view('pages.master.kanban-pairings', compact(
            'editingPairing',
            'pairingCount',
            'projects'
        ));
    }

    public function kanbanPairingsData(Request $request)
    {
        $query = KanbanPairing::query();

        if ($request->filled('project')) {
            $query->where('project', strtoupper(trim((string) $request->input('project'))));
        }

        return DataTables::of($query)
            ->addColumn('action', function (KanbanPairing $kanbanPairing) {
                return view('pages.master.partials.kanban-pairing-actions', compact('kanbanPairing'))->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeKanbanPairing(Request $request)
    {
        if (!$request->has('pairings')) {
            $this->normalizeKanbanPairingRequest($request);
            $values = $request->validate($this->kanbanPairingRules(), [
                'assembly_part.unique' => 'Part number assembly sudah terdaftar pada master pairing lain.',
            ]);

            KanbanPairing::create($values);

            return redirect()->route('dashboard.master.kanban-pairings.index')
                ->with('success', 'Master Kanban Pairing berhasil ditambah.');
        }

        $pairings = array_values(array_map(
            fn ($values) => $this->normalizeKanbanPairingValues(is_array($values) ? $values : []),
            (array) $request->input('pairings', [])
        ));
        $request->merge(['pairings' => $pairings]);

        $validated = $request->validate($this->kanbanPairingBatchRules(), [
            'pairings.*.assembly_part.unique' => 'Part number assembly pada baris :position sudah terdaftar pada master pairing lain.',
            'pairings.*.assembly_part.distinct' => 'Part number assembly pada baris :position duplikat di dalam form.',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['pairings'] as $pairing) {
                KanbanPairing::create($pairing);
            }
        });

        return redirect()->route('dashboard.master.kanban-pairings.index')
            ->with('success', count($validated['pairings']).' Master Kanban Pairing berhasil ditambah.');
    }

    public function updateKanbanPairing(Request $request, KanbanPairing $kanbanPairing)
    {
        $this->normalizeKanbanPairingRequest($request);
        $values = $request->validate($this->kanbanPairingRules($kanbanPairing), [
            'assembly_part.unique' => 'Part number assembly sudah terdaftar pada master pairing lain.',
        ]);

        $kanbanPairing->update($values);

        return redirect()->route('dashboard.master.kanban-pairings.index')
            ->with('success', 'Master Kanban Pairing berhasil diperbarui.');
    }

    public function destroyKanbanPairing(KanbanPairing $kanbanPairing)
    {
        $kanbanPairing->delete();

        return redirect()->route('dashboard.master.kanban-pairings.index')
            ->with('success', 'Master Kanban Pairing berhasil dihapus.');
    }

    private function normalizeLineRequest(Request $request): void
    {
        $request->merge([
            'name' => strtoupper(trim((string) $request->input('name'))),
            'is_active' => $request->boolean('is_active'),
        ]);
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

    private function normalizeKanbanPairingRequest(Request $request): void
    {
        $request->merge($this->normalizeKanbanPairingValues($request->all()));
    }

    private function normalizeKanbanPairingValues(array $values): array
    {
        foreach ([
            'project',
            'product',
            'part_name',
            'part_number_customer',
            'assembly_part',
            'assembly_back_number',
            'painting_part',
            'painting_back_number',
        ] as $field) {
            $values[$field] = strtoupper(trim((string) ($values[$field] ?? '')));
        }

        return $values;
    }

    private function kanbanPairingRules(?KanbanPairing $kanbanPairing = null): array
    {
        return [
            'project' => ['required', 'string', 'max:255'],
            'product' => ['required', 'string', 'max:255'],
            'part_name' => ['required', 'string', 'max:255'],
            'part_number_customer' => ['required', 'string', 'max:255'],
            'assembly_part' => [
                'required',
                'string',
                'max:255',
                Rule::unique('body_kanban_pairings', 'assembly_part')->ignore($kanbanPairing?->id),
            ],
            'assembly_back_number' => ['required', 'string', 'max:255'],
            'painting_part' => ['required', 'string', 'max:255'],
            'painting_back_number' => ['required', 'string', 'max:255'],
            'qty_assy' => ['required', 'integer', 'min:1'],
            'qty_painting' => ['required', 'integer', 'min:1'],
        ];
    }

    private function kanbanPairingBatchRules(): array
    {
        $rules = [
            'pairings' => ['required', 'array', 'min:1', 'max:50'],
        ];

        foreach ($this->kanbanPairingRules() as $field => $fieldRules) {
            $rules['pairings.*.'.$field] = $fieldRules;
        }

        $rules['pairings.*.assembly_part'][] = 'distinct';

        return $rules;
    }

    private function storeInternalPartPhoto($photo, ?string $backNumber, string $partNumber): string
    {
        $baseName = trim((string) ($backNumber ?: $partNumber));
        $baseName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $baseName) ?: 'part';
        $filename = $baseName.'.'.strtolower($photo->extension());
        $directory = public_path('assets/img/pis');

        File::ensureDirectoryExists($directory);
        $photo->move($directory, $filename);

        return $filename;
    }

    private function storeMoldPhoto($photo, string $tag): string
    {
        $safeTag = preg_replace('/[^A-Za-z0-9._-]+/', '_', trim($tag)) ?: 'mold';
        $filename = $safeTag.'-'.Str::uuid().'.'.strtolower($photo->extension());
        $directory = public_path('assets/img/molds');

        File::ensureDirectoryExists($directory);
        $photo->move($directory, $filename);

        return $filename;
    }

    private function deleteMoldPhoto(?string $photo): void
    {
        $filename = basename((string) $photo);

        if ($filename !== '') {
            File::delete(public_path('assets/img/molds/'.$filename));
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;   //pemecah error tokenmismatch
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PisScanListExport;
use App\Models\PisPart;
use App\Models\InternalPart;
use App\Models\PisMutation;
use App\Models\LoadingList;
use App\Models\LoadingListDetail;
use App\Models\PisScan;
use App\Models\PisScanDetail;
use App\Models\PisScanLog;
use App\Models\Customer;
use Carbon\Carbon;


class PisController extends Controller
{
    /**
     * Normalisasi nama dasar file gambar PIS berdasarkan part number customer, jenis, dan dock.
     * Contoh:
     *   cust = "82810-74820-WBY", kind = "OEM", dock = "OTHER"
     *   → "8281074820WBY-OEM-OTHER" (tanda hubung di dalam part number dihapus terlebih dahulu).
     */
    private function buildPisImageBaseName(string $custPart, string $kind, string $dock): string
    {
        $cust = strtoupper(trim($custPart));
        $kind = strtoupper(trim($kind));
        $dock = strtoupper(trim($dock));

        // Hanya izinkan A–Z, 0–9, dan '-' pada input mentah
        $custSanitized = preg_replace('/[^A-Z0-9\-]/', '', $cust);
        // Hapus semua '-' dari part number customer sebelum dijadikan nama file
        $custNoDash = str_replace('-', '', $custSanitized);

        return $custNoDash . '-' . $kind . '-' . $dock;
    }

    /**
     * UI-ONLY PREVIEW MODE (no DB/models).
     * Dummy data provider for all PIS screens.
     */
    private function dummyPisParts(): array
    {
        $make = function (
            int $id,
            string $custPart,
            string $backNo,
            int $qty,
            string $kind,
            string $dock
        ) {
            $o = new \stdClass();
            $o->id = $id;
            $o->part_number_customer = $custPart;
            $o->part_number = 'AIIA-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
            $o->back_number = $backNo;
            $o->qty_kanban = $qty;
            $o->part_kind = $kind;
            $o->part_dock = $dock;
            $o->img_path = $this->buildPisImageBaseName($custPart, $kind, $dock) . '.JPG';
            $o->validasi = ($id % 2 === 0) ? 'Ada' : 'Belum Ada';
            return $o;
        };

        return [
            $make(1, 'CUST-123456', 'BN01', 20, 'OEM', 'OTHER'),
            $make(2, 'CUST-654321', 'BN02', 10, 'GNP', '1L'),
            $make(3, 'CUST-112233', 'BN03', 30, 'OEM', '6I'),
            $make(4, 'CUST-445566', 'BN04', 15, 'GNP', '1N'),
        ];
    }

    private function dummyCustomers(): array
    {
        // Keep shape flexible for views that only need "something"
        return [
            (object) ['customer_code' => 'CUST01', 'customer_name' => 'Customer 01'],
            (object) ['customer_code' => 'CUST02', 'customer_name' => 'Customer 02'],
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // UI-only preview: no DB access
        $customer = $this->dummyCustomers();
        if (!is_array($customer)) {
            $customer = [];
        }
        return view('pis/index', compact('customer'));
    }

    public function packing()
    {
        // UI-only preview: no DB access
        $customer = $this->dummyCustomers();
        if (!is_array($customer)) {
            $customer = [];
        }
        return view('pis/indexpacking', compact('customer'));
    }

  
    //dev-1.0, 20170824, by  yudo, getajax image sekaligus insert ke table mutation
    public function getAjaxImage($image, $type, $dock)
    {
        try {
            // Parse barcode/kanban data
            // Barcode format can vary, but typically contains part number information
            // For kanban scanning, the barcode might be long (220+ chars) or short (12 chars for label)
            
            // Clean barcode: remove control characters (carriage return, line feed, tab, etc.),
            // trim whitespace dan buang karakter "sampah" di awal (non-alfanumerik).
            // Ini untuk kasus scanner yang kadang mengirim karakter tambahan di depan.
            $barcode = preg_replace('/[\x00-\x1F\x7F]/', '', $image); // Remove control characters
            $barcode = trim($barcode); // Remove leading/trailing whitespace
            // Drop leading non-alphanumeric characters (visible garbage at the beginning)
            $barcode = preg_replace('/^[^A-Za-z0-9]+/', '', $barcode);
            
            $deliveryType = strtoupper(trim($type));
            $dockType = strtoupper(trim($dock));

            // Extract part number from barcode
            // Common formats:
            // - Full kanban: extract from position 158-170 (12 chars) or other positions
            // - Short label: first 12 chars
            // - Direct part number: use as-is
            $partNumberFromBarcode = null;
            
            if (strlen($barcode) >= 220) {
                // Full kanban barcode - extract part number from position 158-170
                $partNumberFromBarcode = substr($barcode, 158, 12);
            } elseif (strlen($barcode) >= 12) {
                // Label or shorter barcode - use first 12 chars
                $partNumberFromBarcode = substr($barcode, 0, 12);
            } else {
                // Use barcode as-is if it's short
                $partNumberFromBarcode = $barcode;
            }

            // Clean the part number (remove spaces, special chars if needed)
            $partNumberFromBarcode = trim($partNumberFromBarcode);
            
            // WORKAROUND OPTION: Append character if needed (configurable via config or env)
            // Set to true if database requires trailing character for matching
            // WARNING: This is a workaround - prefer fixing root cause (database data cleanup)
            $appendCharacterWorkaround = config('pis.append_character_workaround', false);
            $appendCharacter = config('pis.append_character', ' '); // Default: space
            
            // Store original before any modifications
            $partNumberOriginal = $partNumberFromBarcode;
            
            if ($appendCharacterWorkaround && !empty($partNumberFromBarcode)) {
                // Only append if not already ending with the character
                if (substr($partNumberFromBarcode, -1) !== $appendCharacter) {
                    $partNumberFromBarcode = $partNumberFromBarcode . $appendCharacter;
                }
            }
            
            // ROOT CAUSE FIX: Handle dashes in database (old code used REPLACE to remove dashes)
            // Database may store part numbers with dashes (e.g., "ABC-123") but scanner sends without dashes
            // Create versions with and without dashes for matching
            $partNumberNoDashes = str_replace('-', '', $partNumberFromBarcode);

            // Look up part in PisPart
            // Try to find by part_number_customer first (customer part number)
            // Use multiple matching strategies to handle various database formats
            $aviPart = PisPart::where(function($query) use ($partNumberFromBarcode, $partNumberNoDashes, $partNumberOriginal, $barcode) {
                // Strategy 1: Direct LIKE match (handles partial matches)
                $query->where('part_number_customer', 'like', '%' . $partNumberFromBarcode . '%')
                    ->orWhere('part_number', 'like', '%' . $partNumberFromBarcode . '%')
                    ->orWhere('back_number', 'like', '%' . $partNumberFromBarcode . '%')
                    // Strategy 2: Match without dashes (database may have dashes, scanner doesn't)
                    ->orWhereRaw('REPLACE(part_number_customer, "-", "") LIKE ?', ['%' . $partNumberNoDashes . '%'])
                    ->orWhereRaw('REPLACE(part_number, "-", "") LIKE ?', ['%' . $partNumberNoDashes . '%'])
                    // Strategy 3: Match original (without appended character) for exact matches
                    ->orWhere('part_number_customer', 'like', $partNumberOriginal . '%')
                    ->orWhere('part_number_customer', '=', $partNumberOriginal)
                    // Strategy 4: Full barcode match for kanban
                    ->orWhere('part_number_kanban', 'like', '%' . $barcode . '%');
            })
            ->where('part_kind', $deliveryType)
            ->where('part_dock', $dockType)
            ->first();

            // If not found with delivery and dock, try without those filters
            if (!$aviPart) {
                $aviPart = PisPart::where(function($query) use ($partNumberFromBarcode, $partNumberNoDashes, $partNumberOriginal, $barcode) {
                    $query->where('part_number_customer', 'like', '%' . $partNumberFromBarcode . '%')
                        ->orWhere('part_number', 'like', '%' . $partNumberFromBarcode . '%')
                        ->orWhere('back_number', 'like', '%' . $partNumberFromBarcode . '%')
                        ->orWhereRaw('REPLACE(part_number_customer, "-", "") LIKE ?', ['%' . $partNumberNoDashes . '%'])
                        ->orWhereRaw('REPLACE(part_number, "-", "") LIKE ?', ['%' . $partNumberNoDashes . '%'])
                        ->orWhere('part_number_customer', 'like', $partNumberOriginal . '%')
                        ->orWhere('part_number_customer', '=', $partNumberOriginal)
                        ->orWhere('part_number_kanban', 'like', '%' . $barcode . '%');
                })->first();
            }

            // If still not found, return error
            if (!$aviPart) {
                \Log::warning('PIS Scan: Part not found', [
                    'barcode' => $barcode,
                    'extracted_part' => $partNumberFromBarcode,
                    'type' => $deliveryType,
                    'dock' => $dockType
                ]);
                return response()->json(["part_number_customer" => ""], 200);
            }

            // Validate that delivery type and dock match (if strict validation is needed)
            // For now, we allow scanning even if type/dock don't match exactly, but log it
            if ($aviPart->part_kind !== $deliveryType || $aviPart->part_dock !== $dockType) {
                \Log::info('PIS Scan: Type/Dock mismatch', [
                    'part' => $aviPart->part_number_customer,
                    'expected_type' => $deliveryType,
                    'actual_type' => $aviPart->part_kind,
                    'expected_dock' => $dockType,
                    'actual_dock' => $aviPart->part_dock
                ]);
            }

            // Get authenticated user info
            $user = Auth::user();
            $npk = $user ? $user->npk : null;
            $customer = $aviPart->customer_code ?? null;

            // Extract serial number from barcode if available
            $serialNo = null;
            if (strlen($barcode) >= 220) {
                // Try to extract serial from various positions in kanban
                $serialNo = substr($barcode, 0, 20); // Adjust based on actual barcode format
            }

            // Prepare mutation data
            $mutationData = [
                'mutation_date' => Carbon::now(),
                'part_number' => $aviPart->part_number,
                'part_number_customer' => $aviPart->part_number_customer,
                'store_location' => $dockType,
                'quantity' => $aviPart->qty_kanban ?? 1,
                'serial_no' => $serialNo ?: $barcode,
                'loading_list' => null, // Will be populated if needed
                'delivery' => $deliveryType,
                'customer' => $customer,
                'dock' => $dockType,
                'npk' => $npk,
                'back_number' => $aviPart->back_number,
                'flag_confirm' => false,
                'created_by' => $user ? $user->id : null,
            ];

            // Save mutation
            DB::beginTransaction();
            try {
                $mutation = PisMutation::createFromScan($mutationData);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('PIS Scan: Failed to save mutation', [
                    'error' => $e->getMessage(),
                    'data' => $mutationData
                ]);
                // Continue anyway - mutation save failure shouldn't block the scan response
            }

            // Get counter (total scans for this part today)
            $counter = PisMutation::getCounter(
                $aviPart->part_number,
                $deliveryType,
                $dockType,
                Carbon::today()->toDateString()
            );

            // Get last scans
            $lastScans = PisMutation::getLastScans($aviPart->part_number, 5);

            // Build image path dengan normalisasi dan hanya untuk file JPEG (.JPG)
            $custRaw = (string) $aviPart->part_number_customer;
            $typeRaw = (string) $aviPart->part_kind;
            $dockRaw = (string) $aviPart->part_dock;

            $custUpper = strtoupper(trim($custRaw));
            $typeUpper = strtoupper(trim($typeRaw));
            $dockUpper = strtoupper(trim($dockRaw));

            // Izinkan hanya A–Z, 0–9, dan tanda hubung (-) pada part_number_customer
            $custSanitized = preg_replace('/[^A-Z0-9\-]/', '', $custUpper);
            $custNoDash = str_replace('-', '', $custSanitized);

            $baseCandidates = [];
            $addBase = function (string $base) use (&$baseCandidates) {
                if ($base !== '' && !in_array($base, $baseCandidates, true)) {
                    $baseCandidates[] = $base;
                }
            };

            // Pola utama: [PART_CUST]-[TYPE]-[DOCK]
            if ($custSanitized !== '') {
                $addBase($custSanitized . '-' . $typeUpper . '-' . $dockUpper);
            }
            if ($custNoDash !== '' && $custNoDash !== $custSanitized) {
                $addBase($custNoDash . '-' . $typeUpper . '-' . $dockUpper);
            }

            // Fallback: hanya part number tanpa type/dock
            if ($custSanitized !== '') {
                $addBase($custSanitized);
            }
            if ($custNoDash !== '' && $custNoDash !== $custSanitized) {
                $addBase($custNoDash);
            }

            // Hanya cari file dengan ekstensi .JPG (JPEG saja, tidak PNG/dll)
            $disk = Storage::disk('pis');
            $resolvedFile = null;
            foreach ($baseCandidates as $base) {
                $fileName = $base . '.JPG';
                if ($disk->exists($fileName)) {
                    $resolvedFile = $fileName;
                    break;
                }
            }

            $imgPath = $resolvedFile
                ? asset('storage/pis/' . $resolvedFile)
                : asset('storage/pis/default.JPG');

            // Get loading list (if applicable for packing)
            // Try to get loading list details based on part number and customer
            $loadingList = [];
            try {
                if ($customer) {
                    // Try to find active loading lists for this customer
                    $activeLoadingLists = LoadingList::where('customer_id', function($query) use ($customer) {
                        $query->select('id')
                            ->from('customers')
                            ->where('code', $customer)
                            ->orWhere('name', 'like', '%' . $customer . '%')
                            ->limit(1);
                    })
                    ->whereDate('delivery_date', '>=', Carbon::today())
                    ->orderBy('delivery_date', 'asc')
                    ->limit(1)
                    ->get();

                    if ($activeLoadingLists->isNotEmpty()) {
                        $ll = $activeLoadingLists->first();
                        // Get loading list details that match this part
                        $llDetails = LoadingListDetail::where('loading_list_id', $ll->id)
                            ->whereHas('customerPart', function($query) use ($aviPart) {
                                $query->where('part_number_customer', $aviPart->part_number_customer)
                                    ->orWhere('part_number', $aviPart->part_number);
                            })
                            ->limit(5)
                            ->get();

                        $loadingList = $llDetails->map(function($detail) {
                            return [
                                'part_name' => $detail->customerPart->part_name ?? $detail->customerPart->part_number_customer ?? '-',
                                'part_number' => $detail->customerPart->part_number_customer ?? '-',
                                'quantity' => $detail->kbn_qty ?? $detail->qty_per_kanban ?? 0,
                                'qty' => $detail->kbn_qty ?? $detail->qty_per_kanban ?? 0,
                            ];
                        })->toArray();
                    }
                }
            } catch (\Exception $e) {
                // Loading list retrieval is optional, log but don't fail
                \Log::debug('PIS Scan: Could not retrieve loading list', [
                    'error' => $e->getMessage(),
                    'customer' => $customer
                ]);
            }

            // Return response
            return response()->json([
                "img_path" => $imgPath,
                "part_number_customer" => $aviPart->part_number_customer,
                "part_number" => $aviPart->part_number,
                "back_number" => $aviPart->back_number,
                "counter" => $counter,
                "last_scan" => $lastScans,
                "loading_list" => $loadingList,
                "qty_kanban" => $aviPart->qty_kanban ?? 1,
            ]);

        } catch (\Exception $e) {
            \Log::error('PIS Scan: Exception in getAjaxImage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'barcode' => $image ?? 'N/A',
                'type' => $type ?? 'N/A',
                'dock' => $dock ?? 'N/A'
            ]);

            return response()->json([
                "part_number_customer" => "",
                "error" => "Scan processing error: " . $e->getMessage()
            ], 500);
        }
    }


    function viewDashboardMutation()
    {

        return view('adminlte::dashboard.mutation');
    }


    function getAjaxMutation()
    {
        // UI-only preview: return dummy mutation data
        return [
            (object) ['new_qty' => 100, 'part_number' => 'AIIA-00001'],
            (object) ['new_qty' => 200, 'part_number' => 'AIIA-00002'],
            (object) ['new_qty' => 150, 'part_number' => 'AIIA-00003'],
        ];
    }

    function PisMasterView()
    {
        // Check authentication
        if (!Auth::check()) {
            return redirect()->route('login.index')->with('error', 'Please login to access PIS Master Data.');
        }

        // Get real data from database
        try {
            $part_piss = PisPart::orderBy('id', 'desc')->get();
            
            // Transform data to match view expectations
            $part_piss = $part_piss->map(function ($item) {
                // Nama file standar: hapus '-' dari part number customer sebelum dijadikan nama file
                $baseName = $this->buildPisImageBaseName(
                    $item->part_number_customer ?? '',
                    $item->part_kind ?? '',
                    $item->part_dock ?? ''
                );
                $item->img_path = $baseName . '.JPG';
                $item->validasi = Storage::disk('pis')->exists($item->img_path) ? 'Ada' : 'Belum Ada';
                return $item;
            });
        } catch (\Exception $e) {
            // Fallback to dummy data if table doesn't exist yet
            $part_piss = collect($this->dummyPisParts());
        }

        // Ensure it's iterable (Collection or array)
        if (!$part_piss instanceof \Illuminate\Support\Collection && !is_array($part_piss)) {
            $part_piss = [];
        }
        
        return view('pis.ViewMasterPis', compact('part_piss'));
    }

    function UpdatePis($id)
    {
        // Check authentication
        if (!Auth::check()) {
            return redirect()->route('login.index')->with('error', 'Please login to edit PIS data.');
        }

        try {
            // Validate ID
            if (empty($id)) {
                \Log::error('UpdatePis: Empty ID provided');
                \Session::flash('flash_type', 'alert-danger');
                \Session::flash('flash_message', 'Invalid PIS ID provided.');
                return redirect('/pis/master');
            }

            // Get real data from database
            $pis = PisPart::findOrFail($id);
            
            // Ensure we have a valid record
            if (!$pis) {
                \Log::error('UpdatePis: Record not found', ['id' => $id]);
                \Session::flash('flash_type', 'alert-danger');
                \Session::flash('flash_message', 'PIS record not found.');
                return redirect('/pis/master');
            }
            
            // Convert to collection for view compatibility
            $part_piss = collect([$pis]);
            
            // Add img_path for view compatibility (mengikuti pola penyimpanan file tanpa '-' di part number)
            $pis->img_path = $this->buildPisImageBaseName(
                $pis->part_number_customer ?? '',
                $pis->part_kind ?? '',
                $pis->part_dock ?? ''
            ) . '.JPG';
            
            // Ensure it's iterable (Collection or array)
            if (!$part_piss instanceof \Illuminate\Support\Collection && !is_array($part_piss)) {
                $part_piss = [];
            }
            
            return view('pis.UpdatePis', compact('part_piss'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('UpdatePis: Model not found', ['id' => $id, 'error' => $e->getMessage()]);
            \Session::flash('flash_type', 'alert-danger');
            \Session::flash('flash_message', 'PIS record not found.');
            return redirect('/pis/master');
        } catch (\Exception $e) {
            \Log::error('UpdatePis: Exception occurred', ['id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            \Session::flash('flash_type', 'alert-danger');
            \Session::flash('flash_message', 'Error loading PIS data: ' . $e->getMessage());
            return redirect('/pis/master');
        }
    }

    function UpdatePisProses(Request $request, $id = null)
    {
        // Check authentication
        if (!Auth::check()) {
            return redirect()->route('login.index')->with('error', 'Please login to update PIS data.');
        }

        try {
            DB::beginTransaction();

            // Get authenticated user
            $user = Auth::user();

            // Get ID from route or request
            $pisId = $id ?? $request->input('id');
            if (!$pisId) {
                throw new \Exception('PIS ID is required');
            }

            // Find the PIS record
            $avp = PisPart::findOrFail($pisId);

            // Validate required fields
            $request->validate([
                'part_number_customer' => 'required|string',
                'back_number' => 'nullable|string',
                'part_kind' => 'required|string|in:OEM,GNP,DANDORY',
                'part_dock' => 'required|string',
                'qty_kanban' => 'required|numeric|min:0',
                'part_picture' => 'nullable|image|mimes:jpeg,jpg,png|max:5120', // 5MB max, optional for update
            ]);

            // Update data
            $part_number_customer = $request->input('part_number_customer');
            $back_number = $request->input('back_number');
            $qty = $request->input('qty_kanban');
            $dock = $request->input('part_dock');
            $type = $request->input('part_kind');

            $avp->part_number_customer = $part_number_customer;
            $avp->back_number = $back_number;
            $avp->qty_kanban = $qty;
            $avp->part_dock = $dock;
            $avp->part_kind = $type;
            $avp->save();

            // Upload image if provided
            if ($request->hasFile('part_picture')) {
                $file = $request->file('part_picture');

                // Nama file standar: hapus '-' dari part number customer sebelum dijadikan nama file
                $baseName = $this->buildPisImageBaseName($part_number_customer, $type, $dock);
                $filesName = $baseName . '.JPG';
                
                // Ensure directory exists
                $directory = storage_path('app/public/pis');
                if (!file_exists($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }
                
                // Delete old file jika ada (baik nama baru maupun nama lama yang masih pakai '-')
                if (Storage::disk('pis')->exists($filesName)) {
                    Storage::disk('pis')->delete($filesName);
                }
                $legacyName = strtoupper($part_number_customer . '-' . $type . '-' . $dock . '.JPG');
                if ($legacyName !== $filesName && Storage::disk('pis')->exists($legacyName)) {
                    Storage::disk('pis')->delete($legacyName);
                }
                
                // Upload new file - use putFileAs for better handling
                Storage::disk('pis')->putFileAs('', $file, $filesName);
            }

            DB::commit();

        \Session::flash('flash_type', 'alert-success');
            \Session::flash('flash_message', 'PIS data updated successfully by ' . $user->name . ' (' . $user->npk . ').');
        return redirect('/pis/master');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Session::flash('flash_type', 'alert-danger');
            \Session::flash('flash_message', 'Error updating PIS data: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    function PisPreview($img)
    {
        // Check authentication
        if (!Auth::check()) {
            return redirect()->route('login.index')->with('error', 'Please login to view PIS images.');
        }

        // Simple check and return asset URL
        $img_path = Storage::disk('pis')->exists($img) ?
            asset('storage/pis/' . $img) :
            asset('storage/pis/default.JPG');

        return view('pis.PisPreview', compact('img_path'));
    }


    function PisSearch()
    {
        // Check authentication
        if (!Auth::check()) {
            return redirect()->route('login.index')->with('error', 'Please login to search PIS data.');
        }

        $input = \Request::all() ?? [];

        try {
            $query = PisPart::query();

            // Filter by part_kind (OEM/GNP)
        $oem = $input['oem'] ?? null;
        $gnp = $input['gnp'] ?? null;
            if ($oem || $gnp) {
                $kinds = array_filter([$oem, $gnp]);
                $query->whereIn('part_kind', $kinds);
            }

            // Filter by part_dock
        $dock_4N = $input['dock_4N'] ?? null;
        $dock_4L = $input['dock_4L'] ?? null;
            if ($dock_4N || $dock_4L) {
                $docks = array_filter([$dock_4N, $dock_4L]);
                $query->whereIn('part_dock', $docks);
            }

            $part_piss = $query->orderBy('id', 'desc')->get();
            
            // Transform data to match view expectations
            $part_piss = $part_piss->map(function ($item) {
                $baseName = $this->buildPisImageBaseName(
                    $item->part_number_customer ?? '',
                    $item->part_kind ?? '',
                    $item->part_dock ?? ''
                );
                $item->img_path = $baseName . '.JPG';
                $item->validasi = Storage::disk('pis')->exists($item->img_path) ? 'Ada' : 'Belum Ada';
                return $item;
            });
        } catch (\Exception $e) {
            // Fallback to dummy data if table doesn't exist
            $part_piss = collect($this->dummyPisParts());
        }

        // Ensure it's iterable (Collection or array)
        if (!$part_piss instanceof \Illuminate\Support\Collection && !is_array($part_piss)) {
            $part_piss = [];
        }

        return view('pis.ViewMasterPis', compact('part_piss'));
    }

    function GetAjaxPartPis()
    {  // dev-1.0 ,Handika, 20171019, get data part no in pis form 
        // Check authentication
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $term = \Request::all();
        $q = $term['q'] ?? '';
        
        // Return empty array if query is too short
        if (strlen((string) $q) < 2) {
            return response()->json([]);
        }

        try {
            // Get from internal_parts table (correct source)
            $parts = InternalPart::where('part_number', 'like', '%' . $q . '%')
                ->whereNotNull('part_number')
                ->where('part_number', '!=', '')
                ->select('part_number')
                ->distinct()
                ->orderBy('part_number', 'asc')
                ->limit(20)
                ->get()
                ->map(function ($item) {
                    return ['part_number' => $item->part_number];
                })
                ->toArray();

            // If we found parts, return them
            if (!empty($parts)) {
                return response()->json($parts);
            }

            // Fallback: get from existing PIS records if internal_parts is empty
            $parts = PisPart::where('part_number', 'like', '%' . $q . '%')
                ->whereNotNull('part_number')
                ->where('part_number', '!=', '')
                ->distinct()
                ->select('part_number')
                ->orderBy('part_number', 'asc')
                ->limit(20)
                ->get()
                ->map(function ($item) {
                    return ['part_number' => $item->part_number];
                })
                ->toArray();

            return response()->json($parts);
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('GetAjaxPartPis error: ' . $e->getMessage(), [
                'query' => $q,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty array if table doesn't exist
            return response()->json([]);
        }
    }

    function validasi()
    { // dev-1.0 ,Fahrul, 20171031, validasi form with ajax
        // Check authentication
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $input = \Request::all();
        $part_number_aiia = $input['part_number_aiia'] ?? null;
        $part_number = $input['part_number'] ?? null;
        $isManual = (string) ($input['is_manual'] ?? '0') === '1';

        try {
            // Manual input should always use manual save flow.
            if ($isManual) {
                return "save1";
            }

            // Check if part exists in internal_parts table (correct source)
            $partExists = false;
            if ($part_number_aiia) {
                $partExists = InternalPart::where('part_number', $part_number_aiia)->exists();
            } elseif ($part_number) {
                $partExists = InternalPart::where('part_number', $part_number)->exists();
            }

            // If part exists in internal_parts, use addpis (save)
            // If part doesn't exist, use addpart (save1)
            return $partExists ? "save" : "save1";
        } catch (\Exception $e) {
            // If internal_parts table doesn't exist, default to "save1" (manual input)
            return "save1";
        }
    }

    /**
     * Get loading list data from DB only (no API call).
     * Returns existing scan + details if found; otherwise { "exists": false }.
     * Frontend should call this first; only call DEA API when exists === false.
     */
    public function getLoadingListData(Request $request)
    {
        $barcode = $request->input('barcode', '');
        $loadingListNumber = $this->normalizeLoadingListNumber($barcode);

        if (empty($loadingListNumber)) {
            return response()->json(['exists' => false, 'message' => 'Invalid barcode'], 400);
        }

        $pisScan = PisScan::where('loading_list_number', $loadingListNumber)
            ->with('details')
            ->first();

        if (!$pisScan) {
            return response()->json(['exists' => false]);
        }

        $items = $pisScan->details->map(function ($detail) {
            $target = (int) ($detail->target_qty ?? 0);
            $scanned = (int) ($detail->scanned_qty ?? 0);
            $remaining = (int) ($detail->remaining_qty ?? max(0, $target - $scanned));
            return [
                'part_number_int' => $detail->part_number_int,
                'part_number_cust' => $detail->part_number_cust,
                'total_qty' => $target,
                'total_kanban_qty' => $target,
                'actual_kanban_qty' => $scanned,
                'remaining' => $remaining,
            ];
        })->values()->all();

        // Saat user scan LL yang sudah pernah disimpan, kirim juga semua LL lain
        // dengan PDS yang sama agar frontend bisa memuat satu grup PDS sekaligus.
        $relatedLoadingLists = [];
        if (!empty($pisScan->pds_number)) {
            $relatedLoadingLists = PisScan::where('pds_number', $pisScan->pds_number)
                ->with('details')
                ->get()
                ->map(function ($scan) {
                    $relatedItems = $scan->details->map(function ($detail) {
                        $target = (int) ($detail->target_qty ?? 0);
                        $scanned = (int) ($detail->scanned_qty ?? 0);
                        $remaining = (int) ($detail->remaining_qty ?? max(0, $target - $scanned));
                        return [
                            'part_number_int' => $detail->part_number_int,
                            'part_number_cust' => $detail->part_number_cust,
                            'total_qty' => $target,
                            'total_kanban_qty' => $target,
                            'actual_kanban_qty' => $scanned,
                            'remaining' => $remaining,
                        ];
                    })->values()->all();

                    return [
                        'loading_list_number' => $scan->loading_list_number,
                        'name' => $scan->loading_list_number,
                        'pds_number' => $scan->pds_number,
                        'items' => $relatedItems,
                    ];
                })
                ->values()
                ->all();
        }

        return response()->json([
            'exists' => true,
            'loading_list_number' => $pisScan->loading_list_number,
            'name' => $pisScan->loading_list_number,
            'pds_number' => $pisScan->pds_number,
            'cycle' => $pisScan->cycle,
            'delivery_date' => $pisScan->delivery_date ? Carbon::parse($pisScan->delivery_date)->format('Y-m-d') : null,
            'shipping_date' => $pisScan->shipping_date ? Carbon::parse($pisScan->shipping_date)->format('Y-m-d') : null,
            'customer_id' => $pisScan->customer_id,
            'items' => $items,
            'related_loading_lists' => $relatedLoadingLists,
        ]);
    }

    /**
     * Normalize barcode to loading_list_number (11 chars, no " A" suffix) for DB consistency.
     */
    private function normalizeLoadingListNumber(string $barcode): string
    {
        // Bersihkan karakter kontrol & whitespace
        $raw = preg_replace('/[\x00-\x1F\x7F]/', '', $barcode);
        $raw = trim($raw);

        // Kadang scanner mengirim karakter "sampah" di awal (bukan huruf/angka),
        // yang menyebabkan 11 digit pertama menjadi bergeser dan tidak match DB.
        // Buang semua karakter non-alfanumerik di bagian depan sebelum ambil 11 digit pertama.
        $raw = preg_replace('/^[^A-Za-z0-9]+/', '', $raw);

        // Prefix z/Z sebelum C — LL 11 karakter diawali C (sama dengan cleanBarcode di PIS UI).
        while (preg_match('/^[zZ]+C/i', $raw)) {
            $raw = preg_replace('/^[zZ]+/i', '', $raw);
        }
        if (preg_match('/^[zZ]\d/', $raw)) {
            $raw = preg_replace('/^[zZ]+/', '', $raw);
        }
        if (preg_match('/^C[A-Za-z0-9]{10}[zZ]+$/i', $raw)) {
            $raw = substr($raw, 0, 11);
        }

        // Ambil maksimal 11 karakter pertama (sesuai format loading list di DB)
        return substr($raw, 0, 11);
    }

    /**
     * Save PIS scan results when loading list is scanned (first time only).
     * If record already exists, do NOT overwrite details — progress is preserved.
     */
    public function savePisScan(Request $request)
    {
        try {
            DB::beginTransaction();

            $loadingListNumber = $this->normalizeLoadingListNumber($request->input('loading_list_number', ''));
            $pdsNumber = $request->input('pds_number');
            $cycle = $request->input('cycle');
            $deliveryDate = $request->input('delivery_date');
            $shippingDate = $request->input('shipping_date');
            $customerId = $request->input('customer_id');
            $customerCode = $request->input('customer_code');
            $customerName = $request->input('customer_name');
            $items = $request->input('items', []);
            $deliveryType = $request->input('delivery_type');
            $dockType = $request->input('dock_type');

            // Try to get customer_id from existing loading_list if available
            if (!$customerId && $loadingListNumber) {
                $existingLoadingList = LoadingList::where('number', $loadingListNumber)->first();
                if ($existingLoadingList && $existingLoadingList->customer_id) {
                    $customerId = $existingLoadingList->customer_id;
                }
            }

            // If still no customer_id, try to find by code or name
            if (!$customerId) {
                if ($customerCode) {
                    $customer = Customer::where('code', $customerCode)->first();
                    if ($customer) {
                        $customerId = $customer->id;
                    }
                } elseif ($customerName) {
                    $customer = Customer::where('name', 'like', '%' . $customerName . '%')->first();
                    if ($customer) {
                        $customerId = $customer->id;
                    }
                }
            }

            // Find or create PIS scan record
            $pisScan = PisScan::firstOrCreate(
                ['loading_list_number' => $loadingListNumber],
                [
                    'pds_number' => $pdsNumber,
                    'cycle' => $cycle,
                    'delivery_date' => $deliveryDate ? Carbon::parse($deliveryDate) : null,
                    'shipping_date' => $shippingDate ? Carbon::parse($shippingDate) : null,
                    'customer_id' => $customerId,
                    'delivery_type' => $deliveryType,
                    'dock_type' => $dockType,
                ]
            );

            // Update customer_id if it was null before
            if (!$pisScan->customer_id && $customerId) {
                $pisScan->customer_id = $customerId;
            }

            // Always keep latest delivery/dock context from UI
            if ($deliveryType) {
                $pisScan->delivery_type = $deliveryType;
            }
            if ($dockType) {
                $pisScan->dock_type = $dockType;
            }
            $pisScan->save();

            // Only write details when this is the first time (new scan). Do NOT overwrite existing
            // scanned_qty/remaining_qty so that progress is never reset on rescan.
            if ($pisScan->wasRecentlyCreated) {
                foreach ($items as $item) {
                    PisScanDetail::updateOrCreate(
                        [
                            'pis_scan_id' => $pisScan->id,
                            'part_number_int' => $item['part_number_int'] ?? null,
                            'part_number_cust' => $item['part_number_cust'] ?? null,
                        ],
                        [
                            'target_qty' => $item['total_qty'] ?? 0,
                            'scanned_qty' => (int) ($item['scanned_qty'] ?? 0),
                            'remaining_qty' => (int) ($item['remaining'] ?? ($item['total_qty'] ?? 0)),
                        ]
                    );
                }

                // Hasil scan pis/index hanya tampil di scanList — hapus dari loading_lists
                // agar tidak muncul di halaman Delivery Monitoring (loadingList.blade.php)
                LoadingList::where('number', $loadingListNumber)->delete();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'PIS scan saved successfully',
                'pis_scan_id' => $pisScan->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('PIS Scan Save Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save PIS scan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update PIS scan detail when part is scanned
     */
    public function updatePisScanDetail(Request $request)
    {
        try {
            DB::beginTransaction();

            $loadingListNumber = $this->normalizeLoadingListNumber($request->input('loading_list_number', ''));
            $partNumberInt = $request->input('part_number_int');
            $partNumberCust = $request->input('part_number_cust');
            $label = $request->input('label'); // raw label/barcode (optional)

            // Find the PIS scan (data from DB only — no API call)
            $pisScan = PisScan::where('loading_list_number', $loadingListNumber)->first();

            if (!$pisScan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'PIS scan not found'
                ], 404);
            }

            // Find the scan detail - match both part numbers to ensure correct record
            $scanDetail = PisScanDetail::where('pis_scan_id', $pisScan->id)
                ->where('part_number_int', $partNumberInt)
                ->where('part_number_cust', $partNumberCust)
                ->lockForUpdate()
                ->first();

            if (!$scanDetail) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'PIS scan detail not found'
                ], 404);
            }

            $targetQty = (int) ($scanDetail->target_qty ?? 0);
            $currentScanned = (int) ($scanDetail->scanned_qty ?? 0);
            if ($targetQty > 0 && $currentScanned >= $targetQty) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'code' => 'TARGET_ALREADY_REACHED',
                    'message' => 'Qty target part ini sudah terpenuhi. Lakukan Confirm Packing di UI sebelum scan berikutnya, atau refresh jika data tidak sinkron.',
                ], 409);
            }

            // Log setiap aktivitas scan per label (tanpa duplikasi summary)
            PisScanLog::create([
                'pis_scan_detail_id' => $scanDetail->id,
                'label' => $label,
                'scan_time' => now(),
            ]);

            // Update agregat (summary) di tabel utama
            $scanDetail->scanned_qty = ($scanDetail->scanned_qty ?? 0) + 1;
            $scanDetail->remaining_qty = max(0, ($scanDetail->target_qty ?? 0) - $scanDetail->scanned_qty);
            $scanDetail->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'PIS scan detail updated',
                'scanned_qty' => $scanDetail->scanned_qty ?? 0,
                'remaining_qty' => $scanDetail->remaining_qty ?? 0
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('PIS Scan Detail Update Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update scan detail: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display PIS scan list page
     */
    public function scanList()
    {
        return view('pis.scanList', [
            'customers' => Customer::all(),
            'manifests' => PisScan::select('pds_number')->distinct()->get()
        ]);
    }

    /**
     * Get PIS scan list for DataTables
     * Grouped by loading_list_number (not PDS number)
     */
    public function getPisScanList()
    {
        try {
            $startDate = request()->query('start_date');
            $endDate = request()->query('end_date');
            $start = null;
            $end = null;
            try {
                $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
            } catch (\Throwable $e) {
                $start = null;
            }
            try {
                $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
            } catch (\Throwable $e) {
                $end = null;
            }

            $scans = PisScan::with(['details'])
                ->when($start || $end, function ($q) use ($start, $end) {
                    $q->whereHas('details', function ($dq) use ($start, $end) {
                        if ($start && $end) {
                            $dq->whereBetween('updated_at', [$start, $end]);
                        } elseif ($start) {
                            $dq->where('updated_at', '>=', $start);
                        } elseif ($end) {
                            $dq->where('updated_at', '<=', $end);
                        }
                    });
                })
                ->latest()
                ->take(2000)
                ->get()
                ->map(function ($scan) {
                    // Calculate totals from all details in this scan
                    $totalTarget = 0;
                    $totalScanned = 0;
                    $latestScanTime = null;
                    
                    foreach ($scan->details as $detail) {
                        $totalTarget += $detail->target_qty ?? 0;
                        $totalScanned += $detail->scanned_qty ?? 0;
                        
                        // Get the latest scan time from updated_at
                        if ($detail->updated_at) {
                            if (!$latestScanTime || $detail->updated_at->gt($latestScanTime)) {
                                $latestScanTime = $detail->updated_at;
                            }
                        }
                    }

                    return (object) [
                        'id' => 'll-' . $scan->id,
                        'loading_list_number' => $scan->loading_list_number,
                        'pds_number' => $scan->pds_number ?: '-',
                        'dock_type' => $scan->dock_type ? (string) $scan->dock_type : '-',
                        'total_target' => $totalTarget,
                        'total_scanned' => $totalScanned,
                        'scan_time' => $latestScanTime ? $latestScanTime->format('Y-m-d H:i') : '-',
                        // Keep the row payload small; modal details are loaded separately via ajax.
                    ];
                })
                ->values();

            return \Yajra\DataTables\Facades\DataTables::of($scans)
                ->addColumn('loading_list_number', function ($item) {
                    $loadingListNumber = $item->loading_list_number ?? '-';
                    // Remove trailing "A" and spaces that may have been appended during scanning
                    return rtrim($loadingListNumber, ' A');
                })
                ->addColumn('progress', function ($item) {
                    $totalTarget = $item->total_target ?? 0;
                    $totalScanned = $item->total_scanned ?? 0;
                    $progressPercentage = ($totalTarget > 0) ? round(($totalScanned / $totalTarget) * 100) : 0;

                    if ($totalScanned >= $totalTarget && $totalTarget > 0) {
                        $statusClass = 'lightgreen';
                    } elseif ($totalScanned > 0) {
                        $statusClass = 'orange';
                    } else {
                        $statusClass = 'red';
                    }

                    $progress = '
                        <div class="text-small font-weight-bold text-muted mb-1 text-center">'
                            . $totalScanned . ' / ' . $totalTarget .
                        '</div>
                        <div class="progress" data-height="20" style="height: 18px;">
                            <div class="progress-bar" role="progressbar"
                                style="width:' . $progressPercentage . '%; background-color: ' . $statusClass . ' !important"
                                aria-valuenow="' . $progressPercentage . '" aria-valuemin="0" aria-valuemax="100">
                                <small class="text-white font-weight-bold">' . $progressPercentage . '%</small>
                            </div>
                        </div>';

                    return $progress;
                })
                ->addColumn('status', function ($item) {
                    $totalTarget = $item->total_target ?? 0;
                    $totalScanned = $item->total_scanned ?? 0;
                    
                    $buttonStyle = 'style="min-width: 120px; padding: 8px 12px; font-size: 13px; font-weight: 500; text-align: center; white-space: nowrap;"';
                
                    // Detail button
                    $detailBtn = '<button class="btn btn-info text-white mr-2 show-pis-detail" ' . $buttonStyle . ' data-loading-list="' . ($item->loading_list_number ?? '') . '">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Detail
                                </button>';
                
                    // Status logic: 
                    // - Complete: semua sudah di-scan (totalScanned >= totalTarget)
                    // - In Progress: sudah ada yang di-scan tapi belum semua (totalScanned > 0 dan < totalTarget)
                    // - In Progress: belum scan sama sekali (totalScanned = 0) - sesuai permintaan user
                    if ($totalScanned >= $totalTarget && $totalTarget > 0) {
                        $statusButton = '<button class="btn btn-success" ' . $buttonStyle . '>
                                            <i class="fas fa-check mr-1"></i>
                                            COMPLETE
                                        </button>';
                    } else {
                        // Baik belum scan sama sekali maupun sudah scan tapi belum semua = IN PROGRESS
                        $statusButton = '<button class="btn btn-outline-warning" ' . $buttonStyle . '>
                                            IN PROGRESS
                                        </button>';
                    }
                
                    return '<div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; align-items: center;">' . $detailBtn . $statusButton . '</div>';
                })
                ->setRowId(function ($item) {
                    return 'row-' . $item->id;
                })
                ->rawColumns(['progress', 'status', 'loading_list_number'])
                ->make(true);
                
        } catch (\Throwable $e) {
            return response()->json([
                'draw' => request()->get('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Unable to load data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Export PIS scan list to Excel (filtered by date range).
     * Uses same "Scan Time" definition as list (latest detail updated_at).
     */
    public function exportPisScanList(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $filenameParts = ['pis-scan-list'];
        if ($startDate) {
            $filenameParts[] = $startDate;
        }
        if ($endDate) {
            $filenameParts[] = $endDate;
        }
        $fileName = implode('_', $filenameParts) . '.xlsx';

        return Excel::download(new PisScanListExport($startDate, $endDate), $fileName);
    }

    /**
     * Get PIS scan details by loading list number
     */
    public function getPisScanDetails(Request $request)
    {
        try {
            $loadingListNumber = $this->normalizeLoadingListNumber($request->get('loading_list_number', ''));
            
            if (!$loadingListNumber) {
                return response()->json(['error' => 'Loading list number is required'], 400);
            }

            $pisScan = PisScan::where('loading_list_number', $loadingListNumber)
                ->with([
                    'customer',
                    'details.logs' => function ($q) {
                        $q->orderByDesc('scan_time')->orderByDesc('id');
                    }
                ])
                ->first();

            if (!$pisScan) {
                return response()->json([
                    'error' => 'PIS scan not found'
                ], 404);
            }

            // Back number tidak tersimpan di scan list; ambil dari master `part_pis`
            // dengan matching part_number_customer yang toleran terhadap perbedaan format
            // (huruf besar/kecil, spasi, strip, dan simbol lain).
            $normalizePartKey = static function (?string $value): string {
                $raw = strtoupper(trim((string) $value));
                return preg_replace('/[^A-Z0-9]/', '', $raw) ?? '';
            };

            $custPartNumbers = $pisScan->details
                ->pluck('part_number_cust')
                ->filter()
                ->map(fn ($v) => strtoupper(trim((string) $v)))
                ->unique()
                ->values();

            $pisParts = PisPart::query()
                ->select('part_number_customer', 'back_number')
                ->get();

            $backNumberByCustPart = [];
            $backNumberByNormalizedCustPart = [];

            foreach ($pisParts as $pisPart) {
                $masterPart = strtoupper(trim((string) ($pisPart->part_number_customer ?? '')));
                $normalizedMasterPart = $normalizePartKey($masterPart);

                if ($masterPart !== '') {
                    $backNumberByCustPart[$masterPart] = $pisPart->back_number;
                }

                if ($normalizedMasterPart !== '') {
                    $backNumberByNormalizedCustPart[$normalizedMasterPart] = $pisPart->back_number;
                }
            }

            $items = $pisScan->details->map(function ($detail) use (
                $backNumberByCustPart,
                $backNumberByNormalizedCustPart,
                $normalizePartKey
            ) {
                // Calculate progress per item based on total_kanban_qty
                // From API response: total_kanban_qty is the target for each item
                // scanned_qty is how many have been scanned
                $targetKanban = $detail->target_qty ?? 0; // This is total_qty from API
                $scannedKanban = $detail->scanned_qty ?? 0;
                $progressPercentage = ($targetKanban > 0) ? round(($scannedKanban / $targetKanban) * 100) : 0;
                
                // Status: 100% if scanned >= target
                $isComplete = ($scannedKanban >= $targetKanban && $targetKanban > 0);

                $custPartKey = strtoupper(trim((string) ($detail->part_number_cust ?? '')));
                $normalizedCustPartKey = $normalizePartKey($custPartKey);
                $backNumber = $custPartKey
                    ? ($backNumberByCustPart[$custPartKey] ?? ($backNumberByNormalizedCustPart[$normalizedCustPartKey] ?? null))
                    : null;
                $scanLogs = $detail->logs->map(function ($log) {
                    return [
                        'label' => $log->label,
                        'scan_time' => optional($log->scan_time)->format('Y-m-d H:i:s'),
                    ];
                })->values();

                return [
                    'part_number_int'      => $detail->part_number_int,
                    'part_number_cust'     => $detail->part_number_cust,
                    'back_number'          => $backNumber,
                    'target_qty'           => $targetKanban,
                    'scanned_qty'          => $scannedKanban,
                    'remaining_qty'        => $detail->remaining_qty ?? max(0, $targetKanban - $scannedKanban),
                    'progress_percentage'  => $progressPercentage,
                    'is_complete'          => $isComplete,
                    // Waktu scan diambil dari updated_at detail (terakhir kali qty di-update)
                    'scanned_at'           => optional($detail->updated_at)->format('Y-m-d H:i'),
                    'scan_logs'            => $scanLogs,
                ];
            });

            return response()->json([
                'loading_list_number' => $pisScan->loading_list_number,
                'pds_number' => $pisScan->pds_number,
                'customer_name' => $pisScan->customer->name ?? null,
                'cycle' => $pisScan->cycle,
                'delivery_date' => $pisScan->delivery_date,
                'items' => $items,
                'total_items' => $items->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to load PIS scan details: ' . $e->getMessage()
            ], 500);
        }
    }
    function addpis(Request $request)
    { //fungsi bukan manual input 
        // Check authentication
        if (!Auth::check()) {
            return redirect()->route('login.index')->with('error', 'Please login to add PIS data.');
        }

        try {
            DB::beginTransaction();

            // Get authenticated user
            $user = Auth::user();

            // Validate required fields
            $request->validate([
                'part_number' => 'required|string',
                'part_number_customer' => 'required|string',
                'back_number' => 'nullable|string',
                'part_kind' => 'required|string|in:OEM,GNP,DANDORY',
                'part_dock' => 'required|string',
                'qty_kanban' => 'required|numeric|min:0',
                'pis_picture' => 'required|image|mimes:jpeg,jpg,png|max:5120', // 5MB max
            ]);

            // Get data from request
            $part_number = $request->input('part_number');
            $part_number_customer = $request->input('part_number_customer');
            $back_number = $request->input('back_number');
            $part_kind = $request->input('part_kind');
            $part_dock = $request->input('part_dock');
            $qty_kanban = $request->input('qty_kanban');

            // FIX: Log and validate part_number is not null
            if (empty($part_number)) {
                \Log::error('PIS addpis: part_number is empty', [
                    'request_data' => $request->all(),
                    'part_number_input' => $request->input('part_number'),
                ]);
                throw new \Exception('Part Number AIIA is required but was not provided.');
            }

            // Save to part_pis
            $pis = new PisPart();
            $pis->part_number = $part_number;
            $pis->part_number_customer = $part_number_customer;
            $pis->back_number = $back_number;
            $pis->part_kind = $part_kind;
            $pis->part_dock = $part_dock;
            $pis->qty_kanban = $qty_kanban;
            $pis->save();

            // Upload image
            if ($request->hasFile('pis_picture')) {
                $file = $request->file('pis_picture');

                // Nama file standar dengan part number customer tanpa '-'
                $baseName = $this->buildPisImageBaseName($part_number_customer, $part_kind, $part_dock);
                $fileName = $baseName . '.JPG';
                
                // Ensure directory exists
                $directory = storage_path('app/public/pis');
                if (!file_exists($directory)) {
                    \File::makeDirectory($directory, 0755, true);
                }
                
                // Delete old file if exists (baru & legacy)
                if (Storage::disk('pis')->exists($fileName)) {
                    Storage::disk('pis')->delete($fileName);
                }
                $legacyName = strtoupper($part_number_customer . '-' . $part_kind . '-' . $part_dock . '.JPG');
                if ($legacyName !== $fileName && Storage::disk('pis')->exists($legacyName)) {
                    Storage::disk('pis')->delete($legacyName);
                }
                
                // Store new file - use putFileAs for better handling
                Storage::disk('pis')->putFileAs('', $file, $fileName);
            }

            DB::commit();

        \Session::flash('flash_type', 'alert-success');
            \Session::flash('flash_message', 'PIS data saved successfully by ' . $user->name . ' (' . $user->npk . ').');
        return redirect('/pis/master');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Session::flash('flash_type', 'alert-danger');
            \Session::flash('flash_message', 'Error saving PIS data: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
    function addpart(Request $request)
    { //fungsi manual input
        // Check authentication
        if (!Auth::check()) {
            return redirect()->route('login.index')->with('error', 'Please login to add PIS data.');
        }

        try {
            DB::beginTransaction();

            // Get authenticated user
            $user = Auth::user();

            // Validate required fields
            $request->validate([
                'hidden_part_no_aiia' => 'required|string',
                'hidden_part_name' => 'required|string',
                'part_number_customer' => 'required|string',
                'back_number' => 'nullable|string',
                'part_kind' => 'required|string|in:OEM,GNP,DANDORY',
                'part_dock' => 'required|string',
                'qty_kanban' => 'required|numeric|min:0',
                'min_stock' => 'nullable|numeric|min:0',
                'max_stock' => 'nullable|numeric|min:0',
                'pis_picture' => 'required|image|mimes:jpeg,jpg,png|max:5120', // 5MB max
            ]);

            // Get data from request
            $part_number_aiia = $request->input('hidden_part_no_aiia');
            $part_name = $request->input('hidden_part_name');
            $part_number_customer = $request->input('part_number_customer');
            $back_number = $request->input('back_number');
            $part_kind = $request->input('part_kind');
            $part_dock = $request->input('part_dock');
            $qty_kanban = $request->input('qty_kanban');
            $min_stock = $request->input('min_stock', 0);
            $max_stock = $request->input('max_stock', 0);

            // Save to internal_parts if it doesn't exist
            try {
                $existingPart = InternalPart::where('part_number', $part_number_aiia)->first();
                if (!$existingPart) {
                    InternalPart::create([
                        'part_number' => $part_number_aiia,
                        'part_name' => $part_name,
                        'back_number' => $back_number,
                        'standard_stock' => $min_stock ?? 0,
                    ]);
                } else {
                    // Update existing part if needed
                    $existingPart->part_name = $part_name;
                    if ($back_number) {
                        $existingPart->back_number = $back_number;
                    }
                    $existingPart->save();
                }
            } catch (\Exception $e) {
                // Table might not exist or part already exists, continue anyway
                // Log error but don't stop the process
            }

            // Save to part_pis
            $pis = new PisPart();
            $pis->part_number = $part_number_aiia;
            $pis->part_number_customer = $part_number_customer;
            $pis->back_number = $back_number;
            $pis->part_kind = $part_kind;
            $pis->part_dock = $part_dock;
            $pis->qty_kanban = $qty_kanban;
            $pis->save();

            // Upload image
            if ($request->hasFile('pis_picture')) {
                $file = $request->file('pis_picture');

                // Nama file standar dengan part number customer tanpa '-'
                $baseName = $this->buildPisImageBaseName($part_number_customer, $part_kind, $part_dock);
                $fileName = $baseName . '.JPG';
                
                // Ensure directory exists
                $directory = storage_path('app/public/pis');
                if (!file_exists($directory)) {
                    \File::makeDirectory($directory, 0755, true);
                }
                
                // Delete old file if exists (baru & legacy)
                if (Storage::disk('pis')->exists($fileName)) {
                    Storage::disk('pis')->delete($fileName);
                }
                $legacyName = strtoupper($part_number_customer . '-' . $part_kind . '-' . $part_dock . '.JPG');
                if ($legacyName !== $fileName && Storage::disk('pis')->exists($legacyName)) {
                    Storage::disk('pis')->delete($legacyName);
                }
                
                // Store new file - use putFileAs for better handling
                Storage::disk('pis')->putFileAs('', $file, $fileName);
            }

            DB::commit();

        \Session::flash('flash_type', 'alert-success');
            \Session::flash('flash_message', 'PIS data saved successfully by ' . $user->name . ' (' . $user->npk . ').');
        return redirect('/pis/master');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Session::flash('flash_type', 'alert-danger');
            \Session::flash('flash_message', 'Error saving PIS data: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Check authentication
        if (!Auth::check()) {
            return redirect()->route('login.index')->with('error', 'Please login to delete PIS data.');
        }

        try {
            $user = Auth::user();
            $pis = PisPart::findOrFail($id);
            
            // Delete associated image if exists (nama baru & nama legacy)
            $newImg = $this->buildPisImageBaseName(
                $pis->part_number_customer ?? '',
                $pis->part_kind ?? '',
                $pis->part_dock ?? ''
            ) . '.JPG';
            $legacyImg = strtoupper(($pis->part_number_customer ?? '') . '-' . ($pis->part_kind ?? '') . '-' . ($pis->part_dock ?? '') . '.JPG');

            foreach ([$newImg, $legacyImg] as $imgPath) {
                if ($imgPath && Storage::disk('pis')->exists($imgPath)) {
                    Storage::disk('pis')->delete($imgPath);
                }
            }
            
            // Delete record
            $pis->delete();

        \Session::flash('flash_type', 'alert-success');
            \Session::flash('flash_message', 'PIS data deleted successfully by ' . $user->name . ' (' . $user->npk . ').');
            return redirect('/pis/master');
        } catch (\Exception $e) {
            \Session::flash('flash_type', 'alert-danger');
            \Session::flash('flash_message', 'Error deleting PIS data: ' . $e->getMessage());
        return redirect('/pis/master');
        }
    }
}

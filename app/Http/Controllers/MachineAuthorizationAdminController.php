<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\CreateMachineTokenRequest;
use App\Http\Requests\Admin\SaveMachinePermissionRequest;
use App\Http\Requests\Admin\SaveMachineRequest;
use App\Http\Requests\Admin\SaveRfidCardRequest;
use App\Models\Line;
use App\Models\Machine;
use App\Models\MachineAccessLog;
use App\Models\MachinePermission;
use App\Models\RfidCard;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class MachineAuthorizationAdminController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()->orderBy('npk')->get(['id', 'npk', 'name']);
        $machines = Machine::query()->with('line')->orderBy('machine_code')->get();
        $editingCard = $request->filled('edit_card')
            ? RfidCard::find($request->integer('edit_card'))
            : null;
        $editingMachine = $request->filled('edit_machine')
            ? Machine::find($request->integer('edit_machine'))
            : null;
        $editingPermission = $request->filled('edit_permission')
            ? MachinePermission::find($request->integer('edit_permission'))
            : null;
        $machineTokens = $editingMachine
            ? $editingMachine->tokens()->latest()->get()
            : collect();
        $lines = Line::query()->orderBy('name')->get(['id', 'name', 'is_active']);

        $counts = [
            'cards' => RfidCard::count(),
            'machines' => Machine::count(),
            'permissions' => MachinePermission::count(),
            'logs' => MachineAccessLog::count(),
        ];

        $activeTab = in_array($request->query('tab'), ['cards', 'machines', 'permissions', 'logs'], true)
            ? $request->query('tab')
            : 'cards';

        return view('pages.machine-auth.index', compact(
            'users',
            'machines',
            'lines',
            'editingCard',
            'editingMachine',
            'editingPermission',
            'machineTokens',
            'counts',
            'activeTab'
        ));
    }

    public function cardsData()
    {
        $query = RfidCard::query()
            ->join('users', 'users.id', '=', 'rfid_cards.user_id')
            ->select('rfid_cards.*', 'users.npk', 'users.name as user_name');

        return DataTables::of($query)
            ->editColumn('is_active', fn (RfidCard $card) => $this->statusBadge($card->is_active))
            ->addColumn('action', fn (RfidCard $card) => view(
                'pages.machine-auth.partials.card-actions',
                compact('card')
            )->render())
            ->rawColumns(['is_active', 'action'])
            ->make(true);
    }

    public function machinesData(Request $request)
    {
        $request->validate([
            'line_id' => ['nullable', 'integer', 'exists:lines,id'],
        ]);

        $query = Machine::query()
            ->leftJoin('lines', 'lines.id', '=', 'machines.line_id')
            ->select('machines.*', 'lines.name as line_name')
            ->withCount(['permissions', 'tokens']);

        if ($request->filled('line_id')) {
            $query->where('machines.line_id', $request->integer('line_id'));
        }

        return DataTables::of($query)
            ->editColumn('machine_code', fn (Machine $machine) => '<strong>'.e($machine->machine_code).'</strong>')
            ->editColumn('line_name', fn (Machine $machine) => $machine->line_name ?? '-')
            ->editColumn('is_active', fn (Machine $machine) => $this->statusBadge($machine->is_active))
            ->addColumn('action', fn (Machine $machine) => view(
                'pages.machine-auth.partials.machine-actions',
                compact('machine')
            )->render())
            ->rawColumns(['machine_code', 'is_active', 'action'])
            ->make(true);
    }

    public function permissionsData()
    {
        $query = MachinePermission::query()
            ->join('users', 'users.id', '=', 'machine_permissions.user_id')
            ->join('machines', 'machines.id', '=', 'machine_permissions.machine_id')
            ->select(
                'machine_permissions.*',
                'users.npk',
                'users.name as user_name',
                'machines.machine_code',
                'machines.machine_name'
            );

        return DataTables::of($query)
            ->addColumn('permission_status', function (MachinePermission $permission) {
                $now = now();

                if (! $permission->is_allowed) {
                    return '<span class="badge bg-danger">Ditolak</span>';
                }

                if ($permission->valid_from && $permission->valid_from->isFuture()) {
                    return '<span class="badge bg-warning text-dark">Belum Berlaku</span>';
                }

                if ($permission->valid_until && ! $permission->valid_until->isAfter($now)) {
                    return '<span class="badge bg-secondary">Kedaluwarsa</span>';
                }

                return '<span class="badge bg-success">Diizinkan</span>';
            })
            ->editColumn('valid_from', fn (MachinePermission $permission) => $permission->valid_from?->format('Y-m-d H:i') ?? '-')
            ->editColumn('valid_until', fn (MachinePermission $permission) => $permission->valid_until?->format('Y-m-d H:i') ?? '-')
            ->addColumn('action', fn (MachinePermission $permission) => view(
                'pages.machine-auth.partials.permission-actions',
                compact('permission')
            )->render())
            ->rawColumns(['permission_status', 'action'])
            ->make(true);
    }

    public function logsData(Request $request)
    {
        $request->validate([
            'result' => ['nullable', Rule::in([
                MachineAccessLog::RESULT_GRANTED,
                MachineAccessLog::RESULT_DENIED,
            ])],
            'machine_id' => ['nullable', 'integer', 'exists:machines,id'],
            'date_from' => ['nullable', 'date'],
            'date_until' => ['nullable', 'date'],
        ]);

        $query = MachineAccessLog::query()
            ->leftJoin('users', 'users.id', '=', 'machine_access_logs.user_id')
            ->leftJoin('machines', 'machines.id', '=', 'machine_access_logs.machine_id')
            ->select(
                'machine_access_logs.*',
                'users.npk',
                'users.name as user_name',
                'machines.machine_name'
            );

        if ($request->filled('result')) {
            $query->where('machine_access_logs.result', $request->string('result'));
        }

        if ($request->filled('machine_id')) {
            $query->where('machine_access_logs.machine_id', $request->integer('machine_id'));
        }

        if ($request->filled('date_from')) {
            $query->where('machine_access_logs.occurred_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }

        if ($request->filled('date_until')) {
            $query->where('machine_access_logs.occurred_at', '<=', Carbon::parse($request->date_until)->endOfDay());
        }

        return DataTables::of($query)
            ->editColumn('result', function (MachineAccessLog $log) {
                return $log->result === MachineAccessLog::RESULT_GRANTED
                    ? '<span class="badge bg-success">GRANTED</span>'
                    : '<span class="badge bg-danger">DENIED</span>';
            })
            ->editColumn('occurred_at', fn (MachineAccessLog $log) => $log->occurred_at?->format('Y-m-d H:i:s'))
            ->editColumn('npk', fn (MachineAccessLog $log) => $log->npk ?? '-')
            ->editColumn('user_name', fn (MachineAccessLog $log) => $log->user_name ?? '-')
            ->rawColumns(['result'])
            ->make(true);
    }

    public function storeCard(SaveRfidCardRequest $request)
    {
        RfidCard::create($request->validated());

        return $this->redirectTo('cards', 'RFID card berhasil ditambahkan.');
    }

    public function updateCard(SaveRfidCardRequest $request, RfidCard $rfidCard)
    {
        $rfidCard->update($request->validated());

        return $this->redirectTo('cards', 'RFID card berhasil diperbarui.');
    }

    public function toggleCard(RfidCard $rfidCard)
    {
        $rfidCard->update(['is_active' => ! $rfidCard->is_active]);

        return $this->redirectTo('cards', 'Status RFID card berhasil diubah.');
    }

    public function storeMachine(SaveMachineRequest $request)
    {
        $machine = Machine::create($request->validated());

        return redirect()->route('machine-auth.index', [
            'tab' => 'machines',
            'edit_machine' => $machine->id,
        ])->with('success', 'Mesin berhasil ditambahkan. Buat token untuk Raspberry Pi.');
    }

    public function updateMachine(SaveMachineRequest $request, Machine $machine)
    {
        $machine->update($request->validated());

        return redirect()->route('machine-auth.index', [
            'tab' => 'machines',
            'edit_machine' => $machine->id,
        ])->with('success', 'Mesin berhasil diperbarui.');
    }

    public function toggleMachine(Machine $machine)
    {
        $machine->update(['is_active' => ! $machine->is_active]);

        return $this->redirectTo('machines', 'Status mesin berhasil diubah.');
    }

    public function storePermission(SaveMachinePermissionRequest $request)
    {
        MachinePermission::create($request->validated());

        return $this->redirectTo('permissions', 'Permission mesin berhasil ditambahkan.');
    }

    public function updatePermission(
        SaveMachinePermissionRequest $request,
        MachinePermission $machinePermission
    ) {
        $machinePermission->update($request->validated());

        return $this->redirectTo('permissions', 'Permission mesin berhasil diperbarui.');
    }

    public function destroyPermission(MachinePermission $machinePermission)
    {
        $machinePermission->delete();

        return $this->redirectTo('permissions', 'Permission mesin berhasil dihapus.');
    }

    public function createToken(CreateMachineTokenRequest $request, Machine $machine)
    {
        if ($request->boolean('rotate_existing')) {
            $machine->tokens()->delete();
        }

        $expiresAt = $request->filled('expires_at')
            ? Carbon::parse($request->input('expires_at'))
            : null;
        $newToken = $machine->createToken(
            $request->input('name'),
            [config('machine_auth.token_ability', 'machine-auth')],
            $expiresAt
        );

        return redirect()->route('machine-auth.index', [
            'tab' => 'machines',
            'edit_machine' => $machine->id,
        ])->with([
            'success' => 'Token mesin berhasil dibuat. Simpan token sekarang karena tidak dapat dilihat kembali.',
            'machine_plain_text_token' => $newToken->plainTextToken,
            'token_machine_code' => $machine->machine_code,
        ]);
    }

    public function revokeToken(Machine $machine, int $token)
    {
        $machine->tokens()->whereKey($token)->firstOrFail()->delete();

        return redirect()->route('machine-auth.index', [
            'tab' => 'machines',
            'edit_machine' => $machine->id,
        ])->with('success', 'Token mesin berhasil dicabut.');
    }

    private function statusBadge(bool $active): string
    {
        return $active
            ? '<span class="badge bg-success">Aktif</span>'
            : '<span class="badge bg-secondary">Nonaktif</span>';
    }

    private function redirectTo(string $tab, string $message)
    {
        return redirect()->route('machine-auth.index', ['tab' => $tab])->with('success', $message);
    }
}

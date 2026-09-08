<?php

namespace App\Services;

use App\Exceptions\MachineAuthProtocolException;
use App\Models\Machine;
use App\Models\MachineAccessLog;
use App\Models\MachineEvent;
use App\Models\MachinePermission;
use App\Models\RfidCard;
use App\Models\User;
use App\Support\MachineAuthorizationReason;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class MachineAuthorizationService
{
    public function authorize(
        Machine $machine,
        string $cardUid,
        string $machineCode,
        string $requestId
    ): array {
        $cardUid = $this->normalizeCardUid($cardUid);
        $machineCode = $this->normalizeMachineCode($machineCode);
        $transactionId = $requestId;

        $existing = MachineAccessLog::with(['user', 'machine'])
            ->where('transaction_id', $transactionId)
            ->first();

        if ($existing) {
            return $this->payloadForExistingRequest($existing, $machine, $cardUid, $machineCode);
        }

        try {
            return DB::transaction(function () use ($machine, $cardUid, $machineCode, $transactionId) {
                $now = CarbonImmutable::now();
                $card = null;
                $user = null;
                $permission = null;
                $reason = MachineAuthorizationReason::AUTHORIZED;
                $authorized = true;
                $httpStatus = 200;

                if ($machineCode !== $machine->machine_code) {
                    $reason = MachineAuthorizationReason::INVALID_REQUEST;
                    $authorized = false;
                    $httpStatus = 403;
                } elseif (! $machine->is_active) {
                    $reason = MachineAuthorizationReason::MACHINE_INACTIVE;
                    $authorized = false;
                } else {
                    $card = RfidCard::with('user')
                        ->where('card_uid', $cardUid)
                        ->first();

                    $user = $card?->user;

                    if (! $card) {
                        $reason = MachineAuthorizationReason::CARD_NOT_FOUND;
                        $authorized = false;
                    } elseif (! $card->is_active) {
                        $reason = MachineAuthorizationReason::CARD_INACTIVE;
                        $authorized = false;
                    } elseif (! $this->userIsActive($user)) {
                        $reason = MachineAuthorizationReason::USER_INACTIVE;
                        $authorized = false;
                    } else {
                        $permission = MachinePermission::where('user_id', $user->id)
                            ->where('machine_id', $machine->id)
                            ->first();

                        if (! $permission) {
                            $reason = MachineAuthorizationReason::PERMISSION_NOT_FOUND;
                            $authorized = false;
                        } elseif (! $permission->is_allowed) {
                            $reason = MachineAuthorizationReason::PERMISSION_DENIED;
                            $authorized = false;
                        } elseif ($permission->valid_from && $permission->valid_from->isAfter($now)) {
                            $reason = MachineAuthorizationReason::PERMISSION_NOT_YET_VALID;
                            $authorized = false;
                        } elseif ($permission->valid_until && ! $permission->valid_until->isAfter($now)) {
                            $reason = MachineAuthorizationReason::PERMISSION_EXPIRED;
                            $authorized = false;
                        }
                    }
                }

                $cacheExpiresAt = $this->cacheExpiresAt($now, $authorized, $permission);

                $log = MachineAccessLog::create([
                    'transaction_id' => $transactionId,
                    'user_id' => $user?->id,
                    'machine_id' => $machine->id,
                    'card_uid' => $cardUid,
                    'machine_code' => $machineCode,
                    'result' => $authorized
                        ? MachineAccessLog::RESULT_GRANTED
                        : MachineAccessLog::RESULT_DENIED,
                    'reason' => $reason,
                    'occurred_at' => $now,
                    'cache_expires_at' => $cacheExpiresAt,
                ]);

                $log->setRelation('user', $user);
                $log->setRelation('machine', $machine);

                return $this->payloadFromLog($log, $httpStatus);
            }, 3);
        } catch (QueryException $exception) {
            $existing = MachineAccessLog::with(['user', 'machine'])
                ->where('transaction_id', $transactionId)
                ->first();

            if (! $existing) {
                throw $exception;
            }

            return $this->payloadForExistingRequest($existing, $machine, $cardUid, $machineCode);
        }
    }

    public function recordEvent(Machine $machine, array $data): array
    {
        $machineCode = $this->normalizeMachineCode($data['machine_code']);
        $cardUid = $this->normalizeCardUid($data['card_uid']);

        if ($machineCode !== $machine->machine_code) {
            throw new MachineAuthProtocolException(403, 'INVALID_REQUEST', 'Token tidak mewakili machine_code tersebut.');
        }

        $accessLog = MachineAccessLog::where('transaction_id', $data['transaction_id'])->first();

        if (! $accessLog) {
            throw new MachineAuthProtocolException(404, 'TRANSACTION_NOT_FOUND', 'Transaction authorization tidak ditemukan.');
        }

        if ((int) $accessLog->machine_id !== (int) $machine->id) {
            throw new MachineAuthProtocolException(403, 'INVALID_REQUEST', 'Transaction bukan milik mesin yang diautentikasi.');
        }

        if ($accessLog->card_uid !== $cardUid) {
            throw new MachineAuthProtocolException(422, 'INVALID_REQUEST', 'card_uid tidak sesuai dengan transaction authorization.');
        }

        $attributes = [
            'event_id' => $data['event_id'],
            'transaction_id' => $accessLog->transaction_id,
            'machine_access_log_id' => $accessLog->id,
            'user_id' => $accessLog->user_id,
            'machine_id' => $machine->id,
            'event_type' => $data['event_type'],
            'card_uid' => $cardUid,
            'machine_code' => $machineCode,
            'occurred_at' => CarbonImmutable::parse($data['occurred_at']),
            'metadata' => $data['metadata'] ?? null,
        ];

        $existing = MachineEvent::where('event_id', $data['event_id'])->first();

        if ($existing) {
            return $this->existingEventResult($existing, $attributes);
        }

        try {
            $event = DB::transaction(fn () => MachineEvent::create($attributes), 3);

            return ['event' => $event, 'duplicate' => false];
        } catch (QueryException $exception) {
            $existing = MachineEvent::where('event_id', $data['event_id'])->first();

            if (! $existing) {
                throw $exception;
            }

            return $this->existingEventResult($existing, $attributes);
        }
    }

    public function syncPayload(Machine $machine): array
    {
        $machine->loadMissing('line');

        $now = CarbonImmutable::now();
        $syncExpiresAt = $now->addHours(max(1, (int) config('machine_auth.sync_cache_ttl_hours', 24)));

        $permissions = MachinePermission::with(['user.rfidCards'])
            ->where('machine_id', $machine->id)
            ->orderBy('user_id')
            ->get();

        $rows = [];

        foreach ($permissions as $permission) {
            $user = $permission->user;

            if (! $user) {
                continue;
            }

            foreach ($user->rfidCards as $card) {
                $reason = $this->permissionReason($machine, $card, $user, $permission, $now);
                $active = $reason === MachineAuthorizationReason::AUTHORIZED;
                $entryExpiresAt = $this->cacheExpiresAt($now, $active, $permission);

                if ($active && $entryExpiresAt->isBefore($syncExpiresAt)) {
                    $syncExpiresAt = $entryExpiresAt;
                }

                $rows[] = [
                    'card_uid' => $card->card_uid,
                    'employee_id' => $user->id,
                    'npk' => $user->npk,
                    'name' => $user->name,
                    'allowed' => (bool) $permission->is_allowed,
                    'active' => $active,
                    'reason' => $reason,
                    'valid_from' => $this->isoDate($permission->valid_from),
                    'valid_until' => $this->isoDate($permission->valid_until),
                    'expires_at' => $this->isoDate($entryExpiresAt),
                ];
            }
        }

        return [
            'machine' => [
                'id' => $machine->id,
                'code' => $machine->machine_code,
                'name' => $machine->machine_name,
                'active' => (bool) $machine->is_active,
                'line' => $machine->line ? [
                    'id' => $machine->line->id,
                    'name' => $machine->line->name,
                ] : null,
            ],
            'generated_at' => $this->isoDate($now),
            'expires_at' => $this->isoDate($syncExpiresAt),
            'permissions' => $rows,
        ];
    }

    private function payloadForExistingRequest(
        MachineAccessLog $log,
        Machine $machine,
        string $cardUid,
        string $machineCode
    ): array {
        if ((int) $log->machine_id !== (int) $machine->id
            || $log->card_uid !== $cardUid
            || $log->machine_code !== $machineCode) {
            throw new MachineAuthProtocolException(
                409,
                'IDEMPOTENCY_CONFLICT',
                'request_id sudah digunakan untuk payload authorization yang berbeda.'
            );
        }

        $status = $log->reason === MachineAuthorizationReason::INVALID_REQUEST ? 403 : 200;

        return $this->payloadFromLog($log, $status);
    }

    private function payloadFromLog(MachineAccessLog $log, int $httpStatus): array
    {
        $log->loadMissing(['user', 'machine.line']);

        return [
            'authorized' => $log->result === MachineAccessLog::RESULT_GRANTED,
            'reason' => $log->reason,
            'transaction_id' => $log->transaction_id,
            'employee' => $log->user ? [
                'id' => $log->user->id,
                'npk' => $log->user->npk,
                'name' => $log->user->name,
            ] : null,
            'machine' => $log->machine ? [
                'id' => $log->machine->id,
                'code' => $log->machine->machine_code,
                'name' => $log->machine->machine_name,
                'line' => $log->machine->line ? [
                    'id' => $log->machine->line->id,
                    'name' => $log->machine->line->name,
                ] : null,
            ] : [
                'id' => null,
                'code' => $log->machine_code,
                'name' => null,
                'line' => null,
            ],
            'cache' => [
                'allowed' => $log->result === MachineAccessLog::RESULT_GRANTED,
                'expires_at' => $this->isoDate($log->cache_expires_at),
            ],
            'http_status' => $httpStatus,
        ];
    }

    private function existingEventResult(MachineEvent $event, array $attributes): array
    {
        $sameEvent = $event->transaction_id === $attributes['transaction_id']
            && (int) $event->machine_id === (int) $attributes['machine_id']
            && $event->event_type === $attributes['event_type']
            && $event->card_uid === $attributes['card_uid']
            && $event->machine_code === $attributes['machine_code'];

        if (! $sameEvent) {
            throw new MachineAuthProtocolException(
                409,
                'IDEMPOTENCY_CONFLICT',
                'event_id sudah digunakan untuk event yang berbeda.'
            );
        }

        return ['event' => $event, 'duplicate' => true];
    }

    private function permissionReason(
        Machine $machine,
        RfidCard $card,
        User $user,
        MachinePermission $permission,
        CarbonImmutable $now
    ): string {
        if (! $machine->is_active) {
            return MachineAuthorizationReason::MACHINE_INACTIVE;
        }

        if (! $card->is_active) {
            return MachineAuthorizationReason::CARD_INACTIVE;
        }

        if (! $this->userIsActive($user)) {
            return MachineAuthorizationReason::USER_INACTIVE;
        }

        if (! $permission->is_allowed) {
            return MachineAuthorizationReason::PERMISSION_DENIED;
        }

        if ($permission->valid_from && $permission->valid_from->isAfter($now)) {
            return MachineAuthorizationReason::PERMISSION_NOT_YET_VALID;
        }

        if ($permission->valid_until && ! $permission->valid_until->isAfter($now)) {
            return MachineAuthorizationReason::PERMISSION_EXPIRED;
        }

        return MachineAuthorizationReason::AUTHORIZED;
    }

    private function cacheExpiresAt(
        CarbonImmutable $now,
        bool $authorized,
        ?MachinePermission $permission = null
    ): CarbonImmutable {
        $expiresAt = $authorized
            ? $now->addHours(max(1, (int) config('machine_auth.allowed_cache_ttl_hours', 24)))
            : $now->addMinutes(max(1, (int) config('machine_auth.denied_cache_ttl_minutes', 60)));

        if ($authorized && $permission?->valid_until && $permission->valid_until->isBefore($expiresAt)) {
            return CarbonImmutable::instance($permission->valid_until);
        }

        return $expiresAt;
    }

    private function userIsActive(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (array_key_exists('is_active', $user->getAttributes())) {
            return (bool) $user->getAttribute('is_active');
        }

        return true;
    }

    private function normalizeCardUid(string $cardUid): string
    {
        return strtoupper(trim($cardUid));
    }

    private function normalizeMachineCode(string $machineCode): string
    {
        return strtoupper(trim($machineCode));
    }

    private function isoDate($value): ?string
    {
        return $value ? CarbonImmutable::instance($value)->utc()->toISOString() : null;
    }
}

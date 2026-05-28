<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterAlamatChute extends Model
{
    protected $table = 'master_alamat_chutes';

    protected $fillable = [
        'part_no_customer',
        'back_no',
        'part_name',
        'alamat_chute',
    ];

    /**
     * Lookup scan pulling: cocokkan part_no_customer + back_no saja (abaikan part_name).
     */
    public static function resolveByPartCustomerAndBackNo(string $partNoCustomer, string $backNo): ?string
    {
        $partNoCustomer = trim($partNoCustomer);
        $backNo = trim($backNo);

        if ($partNoCustomer === '' || $backNo === '') {
            return null;
        }

        $backUpper = strtoupper($backNo);

        $exact = static::query()
            ->where('part_no_customer', $partNoCustomer)
            ->whereRaw('UPPER(TRIM(back_no)) = ?', [$backUpper])
            ->value('alamat_chute');

        if ($exact !== null && trim((string) $exact) !== '') {
            return trim((string) $exact);
        }

        $normalized = strtoupper(str_replace(['-', ' '], '', $partNoCustomer));
        if ($normalized === '') {
            return null;
        }

        $rows = static::query()
            ->whereRaw(
                "UPPER(REPLACE(REPLACE(TRIM(part_no_customer), '-', ''), ' ', '')) = ?",
                [$normalized]
            )
            ->whereRaw('UPPER(TRIM(back_no)) = ?', [$backUpper])
            ->pluck('alamat_chute')
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->values();

        if ($rows->isEmpty()) {
            return null;
        }

        return $rows->first();
    }

    /**
     * Cari alamat chute berdasarkan part customer; back_no & part_name opsional untuk penyempitan.
     *
     * @param  bool  $requireBackNo  Wajibkan match part customer + back_no (untuk scan kanban customer).
     */
    public static function resolveAlamatChute(
        string $partNoCustomer,
        ?string $backNo = null,
        ?string $partName = null,
        bool $requireBackNo = false
    ): ?string {
        $partNoCustomer = trim($partNoCustomer);
        if ($partNoCustomer === '') {
            return null;
        }

        $backNo = trim((string) ($backNo ?? ''));
        $partName = trim((string) ($partName ?? ''));

        if ($requireBackNo && $backNo === '') {
            return null;
        }

        if ($requireBackNo) {
            return static::resolveByPartCustomerAndBackNo($partNoCustomer, $backNo);
        }

        $exact = static::query()
            ->where('part_no_customer', $partNoCustomer)
            ->where('back_no', $backNo)
            ->where('part_name', $partName)
            ->value('alamat_chute');

        if ($exact !== null) {
            return $exact;
        }

        $query = static::query()->where('part_no_customer', $partNoCustomer);

        if ($backNo !== '') {
            $query->where('back_no', $backNo);
        } elseif ($requireBackNo) {
            return null;
        }

        if ($partName !== '') {
            $query->where('part_name', $partName);
        }

        $matches = $query->get(['alamat_chute']);

        if ($matches->count() === 1) {
            return $matches->first()->alamat_chute;
        }

        if ($requireBackNo && $backNo === '') {
            return null;
        }

        $normalized = strtoupper(str_replace(['-', ' '], '', $partNoCustomer));
        if ($normalized === '') {
            return null;
        }

        $flexQuery = static::query()->whereRaw(
            "UPPER(REPLACE(REPLACE(part_no_customer, '-', ''), ' ', '')) = ?",
            [$normalized]
        );

        if ($backNo !== '') {
            $flexQuery->where('back_no', $backNo);
        } elseif ($requireBackNo) {
            return null;
        }

        if ($partName !== '') {
            $flexQuery->where('part_name', $partName);
        }

        $flexMatches = $flexQuery->get(['alamat_chute']);

        if ($flexMatches->count() === 1) {
            return $flexMatches->first()->alamat_chute;
        }

        return null;
    }
}

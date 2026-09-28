<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class InspectMssqlExternal extends Command
{
    protected $signature = 'mssql:check-external
                            {table? : Nama tabel}
                            {--pick= : Nomor picklist NYSJNO}
                            {--bin= : Nomor flight BINID}
                            {--partno= : Nomor part HINB}
                            {--filter= : Where mentah tambahan}
                            {--limit=5 : Jumlah baris}
                            {--cols : Daftar kolom saja}';

    protected $description = 'Cek SELECT bintang SQL Server mssql_external untuk debug Qty KBN';

    public function handle(): int
    {
        $conn = 'mssql_external';
        $limit = max(1, min(50, (int) $this->option('limit', 5)));
        $table = trim((string) ($this->argument('table') ?? ''));
        $picklist = trim((string) $this->option('pick', ''));
        $flight = trim((string) $this->option('bin', ''));
        $part = trim((string) $this->option('partno', ''));
        $where = trim((string) $this->option('filter', ''));

        try {
            DB::connection($conn)->getPdo();
        } catch (Throwable $e) {
            $this->error('Koneksi '.$conn.' gagal: '.$e->getMessage());

            return Command::FAILURE;
        }

        // Mode 1: daftar kolom
        if ($this->option('cols')) {
            if ($table === '') {
                $this->error('Isi nama tabel dulu');

                return Command::INVALID;
            }
            $this->showColumns($conn, $table);

            return Command::SUCCESS;
        }

        // Mode 2: debug picklist (kasus receiving dashboard)
        if ($picklist !== '') {
            return $this->inspectPicklist($conn, $picklist, $limit);
        }

        // Mode 3: SELECT bintang tabel generik
        if ($table === '') {
            $this->line('Contoh pakai:');
            $this->line('  php artisan mssql:check-external IA16NT --cols');
            $this->line('  php artisan mssql:check-external IA16NT --limit=3');
            $this->line('  php artisan mssql:check-external IA31NT --limit=3');
            $this->line('  php artisan mssql:check-external IAA1NT --partno=PART123 --limit=3');
            $this->line('  php artisan mssql:check-external --pick=PICK001 --limit=5');
            $this->line('  php artisan mssql:check-external IA16NT --bin=123 --limit=10');

            return Command::SUCCESS;
        }

        return $this->selectStar($conn, $table, $where, $flight, $part, $limit);
    }

    private function inspectPicklist(string $conn, string $picklist, int $limit): int
    {
        $this->info('== IA31NT (SELECT * WHERE CHR_NUB_NYSJNO = \''.$picklist.'\') ==');
        $rows31 = DB::connection($conn)->table('IA31NT')
            ->where('CHR_NUB_NYSJNO', $picklist)
            ->limit($limit)
            ->get();
        $this->dumpRows($rows31);
        if ($rows31->isEmpty()) {
            $this->warn('IA31NT kosong untuk picklist tersebut.');

            return Command::SUCCESS;
        }

        $flight = $rows31->first()->DEC_COD_BINID ?? null;
        $this->line('DEC_COD_BINID (flight) = '.var_export($flight, true));

        if ($flight === null || $flight === '') {
            return Command::SUCCESS;
        }

        $this->info('');
        $this->info('== IA16NT (SELECT * WHERE DEC_COD_BINID = \''.$flight.'\' LIMIT '.$limit.') ==');
        $this->showColumns($conn, 'IA16NT');
        $rows16 = DB::connection($conn)->table('IA16NT')
            ->where('DEC_COD_BINID', $flight)
            ->limit($limit)
            ->get();
        $this->dumpRows($rows16);

        // Untuk tiap part, tampilkan 1 baris IAA1NT SELECT * agar terlihat kandidat kolom Qty/Kanban
        $parts = $rows16->pluck('CHR_COD_HINB')->filter()->unique()->take(3)->values();
        foreach ($parts as $pn) {
            $this->info('');
            $this->info('== IAA1NT (SELECT * WHERE CHR_COD_HINB = \''.$pn.'\' LIMIT 1) ==');
            $rowA = DB::connection($conn)->table('IAA1NT')
                ->where('CHR_COD_HINB', $pn)
                ->limit(1)
                ->get();
            $this->dumpRows($rowA);
        }

        $this->info('');
        $this->info('Bandingkan dengan mapping showModal saat ini: supplier=CHR_NUB_MLNO, part=CHR_COD_HINB, qty_ordered=DEC_SUR_KKSUH, uom=CHR_INF_HTTN, back_number=IAA1NT.CHR_NUB_SBNG');
        $this->line('Cari di dump di atas: kolom mana yang nilainya = Quantity/Kanban, dan kolom mana yang = Qty Ordered (KBN).');

        return Command::SUCCESS;
    }

    private function selectStar(string $conn, string $table, string $where, string $flight, string $part, int $limit): int
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            $this->error('Nama tabel tidak valid: '.$table);

            return Command::INVALID;
        }

        $this->info('== '.$table.' (SELECT * LIMIT '.$limit.') ==');
        $this->showColumns($conn, $table);

        $q = DB::connection($conn)->table($table);
        // Default tampilkan data terbaru dulu agar tidak dapat baris tua 2017 (KKSUK masih .0000)
        if ($this->columnExists($conn, $table, 'DEC_COD_BINID')) {
            $q->orderByDesc('DEC_COD_BINID');
        } elseif ($this->columnExists($conn, $table, 'CHR_DAY_NYUD')) {
            $q->orderByDesc('CHR_DAY_NYUD');
        }
        if ($flight !== '' && $this->columnExists($conn, $table, 'DEC_COD_BINID')) {
            $q->where('DEC_COD_BINID', $flight);
            $this->line('Filter: DEC_COD_BINID = '.$flight);
        }
        if ($part !== '' && $this->columnExists($conn, $table, 'CHR_COD_HINB')) {
            $q->where('CHR_COD_HINB', $part);
            $this->line('Filter: CHR_COD_HINB = '.$part);
        }
        if ($where !== '') {
            $q->whereRaw($where);
            $this->line('Filter: '.$where);
        }

        $this->dumpRows($q->limit($limit)->get());

        return Command::SUCCESS;
    }

    private function showColumns(string $conn, string $table): void
    {
        try {
            $cols = DB::connection($conn)
                ->table('INFORMATION_SCHEMA.COLUMNS')
                ->where('TABLE_NAME', $table)
                ->orderBy('ORDINAL_POSITION')
                ->get(['COLUMN_NAME', 'DATA_TYPE', 'CHARACTER_MAXIMUM_LENGTH', 'NUMERIC_PRECISION', 'NUMERIC_SCALE']);
        } catch (Throwable $e) {
            $this->warn('Gagal baca INFORMATION_SCHEMA: '.$e->getMessage());

            return;
        }

        if ($cols->isEmpty()) {
            $this->warn('Kolom tidak ditemukan untuk tabel '.$table.'.');

            return;
        }

        $this->table(
            ['COLUMN_NAME', 'DATA_TYPE', 'CHAR_LEN', 'NUM_PREC', 'NUM_SCALE'],
            $cols->map(fn ($c) => [
                $c->COLUMN_NAME,
                $c->DATA_TYPE,
                $c->CHARACTER_MAXIMUM_LENGTH,
                $c->NUMERIC_PRECISION,
                $c->NUMERIC_SCALE,
            ])->all()
        );
    }

    private function columnExists(string $conn, string $table, string $column): bool
    {
        try {
            return DB::connection($conn)
                ->table('INFORMATION_SCHEMA.COLUMNS')
                ->where('TABLE_NAME', $table)
                ->where('COLUMN_NAME', $column)
                ->exists();
        } catch (Throwable $e) {
            return false;
        }
    }

    private function dumpRows($rows): void
    {
        if ($rows->isEmpty()) {
            $this->warn('(0 baris)');

            return;
        }

        $arr = $rows->map(fn ($r) => (array) $r)->all();
        $headers = array_keys($arr[0]);
        $body = array_map(fn ($row) => array_map(fn ($v) => $this->stringify($v), $row), $arr);

        // Tabel console bisa kepotong kalau kolom banyak — tampilkan ringkas + JSON baris pertama
        $this->table($headers, $body);
        $this->line('Jumlah baris: '.count($arr));
        $this->line('JSON baris pertama (copy-paste mudah):');
        $this->line(json_encode($arr[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function stringify($v): string
    {
        if ($v === null) {
            return 'NULL';
        }
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d H:i:s');
        }
        if (is_bool($v)) {
            return $v ? '1' : '0';
        }
        $s = (string) $v;

        return strlen($s) > 60 ? substr($s, 0, 57).'...' : $s;
    }
}

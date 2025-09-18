<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Traits\prodPlanOps;
use App\Models\ProductionPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UpdateProductionPlanCommand extends Command
{
    use prodPlanOps;

    /**
     * The name and signature of the console command.
     *
     * Options:
     *  --date=YYYY-MM-DD  : proses hanya 1 tanggal tertentu
     *  --days=1           : proses mulai hari ini sebanyak N hari ke depan (abaikan jika --date dipakai)
     *  --force            : paksa refresh walau signature sama
     */
    protected $signature = 'update:plan {--date=} {--days=1} {--force}';

    /**
     * The console command description.
     */
    protected $description = 'Update production plan data (sync with external MSSQL + compute working/balance time)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting production plan update...');

        try {
            $specificDate  = $this->option('date');
            $days          = max(1, (int) $this->option('days'));
            $forceRefresh  = (bool) $this->option('force');

            if ($specificDate) {
                // Proses 1 tanggal spesifik
                $date = Carbon::parse($specificDate)->startOfDay();
                $this->updateProductionPlan($date, $forceRefresh);
            } else {
                // Proses mulai hari ini selama N hari
                for ($i = 0; $i < $days; $i++) {
                    $date = now()->startOfDay()->addDays($i);
                    $this->updateProductionPlan($date, $forceRefresh);
                }
            }

            $this->info('Production plan update completed successfully!');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Production plan update failed: '.$e->getMessage());
            Log::error('Production plan command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    /**
     * Update production plan untuk satu tanggal (selaras dengan Dashboard Controller).
     */
    protected function updateProductionPlan(Carbon $selectedDate, bool $forceRefresh = false): void
    {
        $this->line("— Processing date: ".$selectedDate->format('Y-m-d'));

        // Window perhitungan (sama seperti controller)
        $today = $selectedDate->copy()->startOfDay();
        $start = $today->copy()->addHours(10);   // 10:00 di selected date
        $end   = $start->copy()->addDay();       // 10:00 di hari berikutnya

        // Sumber mapping/backNo & prod time dari TRAIT (bukan hardcode di command)
        $allBackNos = collect($this->backNosByLine)->flatten()->unique()->values();

        // Cek last update lokal
        $lastUpdate = ProductionPlan::where('plan_date', $today->toDateString())->max('updated_at');

        // === Cek signature MSSQL (selaras dengan controller) ===
        $sigKey  = 'pulling:sig:'.$selectedDate->format('Ymd');
        $curSig  = $this->externalSignature($selectedDate, $allBackNos);
        $prevSig = Cache::get($sigKey);

        $shouldRefresh = $forceRefresh || !$lastUpdate || !$prevSig || ($curSig && $curSig !== $prevSig);

        if (!$shouldRefresh) {
            $this->info("No upstream change; using cached plan. (last local update: ".($lastUpdate? $lastUpdate->format('Y-m-d H:i:s') : '—').")");
            return;
        }

        try {
            DB::beginTransaction();

            // Ambil data eksternal (pakai fungsi dari TRAIT, sudah handle 6I/STR & threshold 10:40)
            $rawData = $this->fetchWithLaravelDB($today, $start, $allBackNos, $this->prodTimeByBackNo, $selectedDate);
            if ($rawData->isEmpty()) {
                throw new \Exception('No production data available from MSSQL for '.$selectedDate->format('Y-m-d'));
            }
            $this->info('Fetched '.$rawData->count().' raw rows.');

            // Agregasi & normalisasi (pakai TRAIT)
            $processedData = $this->processRawData($rawData, $start, $end);
            $this->info('Processed into '.$processedData->count().' grouped records.');

            // Tulis/Update ke ProductionPlan (pakai TRAIT)
            // NOTE: Di TRAIT: dock STR -> delivery_time dikurangi 30 menit, selain itu 60 menit.
            $this->updateProductionData($processedData, $this->backNosByLine, $today);

            DB::commit();

            // Simpan signature terbaru (durasi sama seperti controller: 6 jam)
            if ($curSig) {
                Cache::put($sigKey, $curSig, now()->addHours(6));
            }

            $this->info('Successfully updated production data for '.$selectedDate->format('Y-m-d').' (upstream changed).');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Failed to update data for '.$selectedDate->format('Y-m-d').': '.$e->getMessage());
            Log::error('Production data processing failed', [
                'date'  => $selectedDate->format('Y-m-d'),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
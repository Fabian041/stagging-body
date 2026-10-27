<?php

namespace App\Jobs;


use App\Services\KanbanPdfProcessor;
use App\Services\Mmki\MmkiKanbanPdfProcessor;
use App\Services\All\AllKanbanPdfProcessor;
use App\Services\KanbanPdfOptimizer;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;



class ProcessKanbanUpload implements ShouldQueue
{


    use Dispatchable;
    use Queueable;



    public array $files;


    public string $jobId;


    public string $customer;


    public function __construct(

        array $files,

        string $jobId,

        string $customer = 'TMMIN'

    )
    {

        $this->files = $files;

        $this->jobId = $jobId;

        $customer = strtoupper(trim($customer));

        $this->customer = in_array($customer, ['TMMIN', 'MMKI', 'ALL'], true)
            ? $customer
            : 'TMMIN';

    }





    public function handle(

        KanbanPdfProcessor $tmminProcessor,

        MmkiKanbanPdfProcessor $mmkiProcessor,

        AllKanbanPdfProcessor $allProcessor,

        KanbanPdfOptimizer $optimizer,

    )
    {


        // Form MMKI memakai service sendiri agar split kanban sesuai
        // (layout MMKI dan TMMIN/YMMIN tidak sama).
        // Form ALL memakai service baru sendiri (hanya crop blank atas/bawah).

        $cust = strtoupper(trim($this->customer ?? ''));

        $processor = $cust === 'ALL'
            ? $allProcessor
            : ($cust === 'MMKI' ? $mmkiProcessor : $tmminProcessor);


        $dir = storage_path('app/temp-kanban');
        $cancelPath = $dir.'/'.$this->jobId.'.cancel';
        $cancelledPath = $dir.'/'.$this->jobId.'.cancelled';

        $cleanupInputs = function () {
            foreach($this->files as $file)
            {
                if(is_string($file) && $file !== '' && file_exists($file))
                {
                    @unlink($file);
                }
            }
            @unlink($dir.'/'.$this->jobId.'.meta.json');
        };

        // Batal total sebelum mulai: job sudah di-cancel dari UI.
        if(file_exists($cancelPath))
        {
            $cleanupInputs();
            @file_put_contents($cancelledPath, date('Y-m-d H:i:s'));

            return;
        }


        logger(
            "QUEUE START ".$this->jobId
        );



        $filename =

            $processor->process(

                $this->files,

                $this->jobId

            );



        // Batal total di tengah jalan: buang hasil, jangan tulis marker DONE.
        if(file_exists($cancelPath))
        {
            if(isset($filename) && is_string($filename) && $filename !== '')
            {
                $out = $dir.'/'.basename($filename);
                if(file_exists($out)) @unlink($out);
            }
            $cleanupInputs();
            @file_put_contents($cancelledPath, date('Y-m-d H:i:s'));

            return;
        }


        /*
            marker selesai
        */


        /*
            arsip HASIL generate ke kanban-source dengan nama asli
            (untuk fetch dari dashboard delivery per PDS/manifest).
            Arsip dulu, marker DONE belakangan: DONE terlihat = info arsip pasti sudah ada.
        */

        // Optimasi ukuran file hasil (opsional; fallback ke asli bila gagal/tidak lebih kecil).
        if (is_string($filename) && $filename !== '') {
            $beforePath = $dir . DIRECTORY_SEPARATOR . basename((string) $filename);
            $beforeSize = is_file($beforePath) ? (int) @filesize($beforePath) : null;
            $optimized = $optimizer->optimizeFiles([(string) $filename], $dir);
            if (! empty($optimized[0]) && is_string($optimized[0])) {
                $filename = $optimized[0];
            }
            $afterPath = $dir . DIRECTORY_SEPARATOR . basename((string) $filename);
            $afterSize = is_file($afterPath) ? (int) @filesize($afterPath) : null;
            try {
                logger()->info('KanbanPdfOptimizer size', [
                    'job' => $this->jobId,
                    'file' => (string) $filename,
                    'before_bytes' => $beforeSize,
                    'after_bytes' => $afterSize,
                ]);
            } catch (\Throwable $ignored) {
            }
        }

        $archived = \App\Support\KanbanSourceArchive::archiveJobOutputs(
            $this->jobId,
            \App\Support\KanbanSourceArchive::parseDoneMarker((string) $filename)
        );

        // Hemat space: setelah arsip berhasil, hapus file hasil temp (arsip ada di kanban-source).
        if (! empty($archived) && is_string($filename) && $filename !== '') {
            @unlink($dir . DIRECTORY_SEPARATOR . basename((string) $filename));
        }


        file_put_contents(

            storage_path(
                'app/temp-kanban/'
                .$this->jobId
                .'.done'
            ),

            $filename

        );





        /*
            hapus input sementara

        */


        foreach($this->files as $file)
        {


            if(file_exists($file))
            {

                unlink($file);

            }


        }

        @unlink($dir.'/'.$this->jobId.'.meta.json');



        logger(
            "QUEUE FINISH ".$filename
        );


    }


}
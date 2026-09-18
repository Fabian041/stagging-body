<?php

namespace App\Jobs;


use App\Services\KanbanPdfProcessor;
use App\Services\Mmki\MmkiKanbanPdfProcessor;
use App\Services\All\AllKanbanPdfProcessor;


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
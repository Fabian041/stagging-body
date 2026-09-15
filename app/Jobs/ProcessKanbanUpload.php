<?php

namespace App\Jobs;


use App\Services\KanbanPdfProcessor;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;



class ProcessKanbanUpload implements ShouldQueue
{


    use Dispatchable;
    use Queueable;



    public array $files;


    public string $jobId;




    public function __construct(

        array $files,

        string $jobId

    )
    {

        $this->files = $files;

        $this->jobId = $jobId;

    }





    public function handle(

        KanbanPdfProcessor $processor

    )
    {


        logger(
            "QUEUE START ".$this->jobId
        );



        $filename =

            $processor->process(

                $this->files,

                $this->jobId

            );




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



        logger(
            "QUEUE FINISH ".$filename
        );


    }


}
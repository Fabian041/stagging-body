<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;


class KanbanPdfProcessor
{


    public function __construct(
        protected KanbanPdfSplitter $splitter
    )
    {

    }



    public function process(
        array $files,
        string $jobId
    )
    {


        $output = new Fpdi();

        $output->setPrintHeader(false);
        $output->setPrintFooter(false);
        $output->SetMargins(0, 0, 0);
        $output->SetAutoPageBreak(false, 0);
        $output->SetCreator('BELLA');
        $output->SetAuthor('BELLA');
        $output->SetTitle('Kanban Split Result');

        logger()->info('KanbanPdfProcessor v2 start', [
            'job' => $jobId,
            'files' => count($files),
        ]);

        $totalPages = 0;


        foreach($files as $file)
        {

            if (! is_string($file) || ! is_file($file)) {
                continue;
            }

            $totalPages +=
                $this->splitter->split(
                    $output,
                    $file
                );

        }



        $filename =
            'kanban_result_'
            .$jobId
            .'.pdf';



        $dir =
            storage_path(
                'app/temp-kanban'
            );

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir . DIRECTORY_SEPARATOR . $filename;


        $output->Output(
            $path,
            'F'
        );

        logger()->info('KanbanPdfProcessor v2 done', [
            'job' => $jobId,
            'file' => $filename,
            'pages' => $totalPages,
        ]);


        return $filename;

    }


}

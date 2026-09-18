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

        // Pass 1: kumpulkan semua region (detect + skip kosong +
        // press ke isi) agar ukuran kertas SERAGAM se-job.
        $collected = []; // ['file'=>, 'regions'=>[]]

        foreach($files as $file)
        {

            if (! is_string($file) || ! is_file($file)) {
                continue;
            }

            try {
                $regions = $this->splitter->collectRegions($file);
            } catch (\Throwable $e) {
                logger()->warning('Kanban collect failed', [
                    'file' => basename((string) $file),
                    'error' => $e->getMessage(),
                ]);

                $regions = [];
            }

            $collected[] = ['file' => $file, 'regions' => $regions];

        }

        $all = [];
        foreach ($collected as $c) {
            foreach ($c['regions'] as $r) {
                $all[] = $r;
            }
        }

        [$uniL, $uniP] = $this->splitter->uniformSizes($all);

        logger()->info('Kanban uniform size', [
            'job' => $jobId,
            'regions' => count($all),
            'uniL' => array_map(static fn ($v) => round((float) $v, 1), $uniL),
            'uniP' => array_map(static fn ($v) => round((float) $v, 1), $uniP),
        ]);

        // Pass 2: render semua dengan kertas seragam, konten di tengah.
        $totalPages = 0;

        foreach ($collected as $c) {
            $file = $c['file'];
            $regions = $c['regions'];

            if (empty($regions)) {
                // Fallback: salin apa adanya via jalur lama.
                $totalPages += $this->splitter->split($output, $file);

                continue;
            }

            $byOrient = ['L' => [], 'P' => []];
            foreach ($regions as $r) {
                $byOrient[$r['w'] > $r['h'] ? 'L' : 'P'][] = $r;
            }

            if (! empty($byOrient['L']) && $uniL[0] > 0 && $uniL[1] > 0) {
                $totalPages += $this->splitter->renderRegions($output, $file, $byOrient['L'], $uniL[0], $uniL[1]);
            }
            if (! empty($byOrient['P']) && $uniP[0] > 0 && $uniP[1] > 0) {
                $totalPages += $this->splitter->renderRegions($output, $file, $byOrient['P'], $uniP[0], $uniP[1]);
            }
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

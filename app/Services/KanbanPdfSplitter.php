<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;

class KanbanPdfSplitter
{


    public function __construct(
        protected KanbanRegionDetector $detector
    )
    {

    }



    public function split(
        Fpdi $pdf,
        string $file
    ): int
    {


        $pageCount =
            $pdf->setSourceFile($file);

        $added = 0;



        for(
            $page = 1;
            $page <= $pageCount;
            $page++
        )
        {


            $template =
                $pdf->importPage($page);


            $size =
                $pdf->getTemplateSize($template);

            $pageW = (float) ($size['width'] ?? 0);
            $pageH = (float) ($size['height'] ?? 0);

            if ($pageW <= 0 || $pageH <= 0) {
                continue;
            }

            $regions = null;
            try {
                $regions =
                    $this->detector->detect(
                        $file,
                        $page,
                        ['width' => $pageW, 'height' => $pageH]
                    );
            } catch (\Throwable $e) {
                logger()->warning('Kanban split: detect failed', [
                    'file' => basename($file),
                    'page' => $page,
                    'error' => $e->getMessage(),
                ]);

                $regions = [];
            }


            if (empty($regions)) {
                $pdf->AddPage(
                    $pageW > $pageH ? 'L' : 'P',
                    [$pageW, $pageH]
                );
                $pdf->useTemplate($template, 0, 0, $pageW, $pageH);
                $added++;

                logger()->warning('Kanban split: no regions, page copied as-is', [
                    'file' => basename($file),
                    'page' => $page,
                ]);

                continue;
            }

            logger()->info('Kanban split page', [
                'file' => basename($file),
                'page' => $page,
                'regions' => count($regions),
            ]);


            foreach($regions as $region)
            {
                $w = (float) ($region['width'] ?? 0);
                $h = (float) ($region['height'] ?? 0);
                $x = (float) ($region['x'] ?? 0);
                $y = (float) ($region['y'] ?? 0);

                if ($w <= 0 || $h <= 0) {
                    continue;
                }

                $x = max(0, min($x, $pageW - 1));
                $y = max(0, min($y, $pageH - 1));
                $w = min($w, $pageW - $x);
                $h = min($h, $pageH - $y);

                if ($w <= 0 || $h <= 0) {
                    continue;
                }

                $pdf->AddPage(
                    $w > $h ? 'L' : 'P',
                    [$w, $h]
                );

                $pdf->useTemplate(
                    $template,
                    -$x,
                    -$y,
                    $pageW,
                    $pageH
                );

                $added++;

            }


        }

        return $added;


    }


}

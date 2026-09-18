<?php

namespace App\Services\All;

use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Fallback processor khusus tab ALL (auto).
 *
 * File ini sebelumnya hilang (folder app/Services/All kosong) sehingga
 * ProcessKanbanUpload untuk customer ALL akan fatal "class not found".
 * Implementasi ini sengaja dibuat TERISOLASI: hanya merge halaman apa adanya
 * tanpa logika deteksi TMMIN/MMKI, jadi tidak mengganggu service lain.
 */
class AllKanbanPdfProcessor
{
    public function process(array $files, string $jobId): string
    {
        $output = new Fpdi();
        $output->setPrintHeader(false);
        $output->setPrintFooter(false);
        $output->SetMargins(0, 0, 0);
        $output->SetAutoPageBreak(false, 0);
        $output->SetCreator('BELLA');
        $output->SetAuthor('BELLA');
        $output->SetTitle('Kanban ALL Result');

        logger()->info('AllKanbanPdfProcessor start', [
            'job' => $jobId,
            'files' => count($files),
        ]);

        $totalPages = 0;

        foreach ($files as $file) {
            if (! is_string($file) || ! is_file($file)) {
                continue;
            }

            try {
                $pageCount = $output->setSourceFile($file);
            } catch (\Throwable $e) {
                logger()->warning('All collect failed', [
                    'file' => basename((string) $file),
                    'error' => $e->getMessage(),
                ]);
                continue;
            }

            for ($page = 1; $page <= $pageCount; $page++) {
                try {
                    $template = $output->importPage($page);
                    $size = $output->getTemplateSize($template);
                } catch (\Throwable $e) {
                    continue;
                }

                $pageW = (float) ($size['width'] ?? 0);
                $pageH = (float) ($size['height'] ?? 0);

                if ($pageW <= 0 || $pageH <= 0) {
                    continue;
                }

                $output->AddPage($pageW > $pageH ? 'L' : 'P', [$pageW, $pageH]);
                $output->useTemplate($template, 0, 0, $pageW, $pageH);
                $totalPages++;
            }
        }

        $filename = 'kanban_all_result_'.$jobId.'.pdf';
        $dir = storage_path('app/temp-kanban');

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $output->Output($dir.DIRECTORY_SEPARATOR.$filename, 'F');

        logger()->info('AllKanbanPdfProcessor done', [
            'job' => $jobId,
            'file' => $filename,
            'pages' => $totalPages,
        ]);

        return $filename;
    }
}

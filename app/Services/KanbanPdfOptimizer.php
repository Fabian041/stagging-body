<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;

class KanbanPdfOptimizer
{
    protected ?string $ghostscript = null;
    protected ?string $qpdf = null;
    protected bool $detected = false;

    public function optimizeFiles(array $files, string $dir): array
    {
        if (! $this->enabled()) {
            return array_values($files);
        }

        $out = [];
        foreach ($files as $name) {
            $name = basename((string) $name);
            if ($name === '' || $name === '.' || $name === '..') {
                $out[] = $name;
                continue;
            }

            $path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
            if (! is_file($path)) {
                $out[] = $name;
                continue;
            }

            $optimizedName = $this->optimizeFile($path, $name);
            $out[] = $optimizedName;
        }

        return array_values($out);
    }

    public function optimizeFile(string $path, ?string $name = null): string
    {
        if (! $this->enabled()) {
            return $name ?? basename($path);
        }

        $name = $name ?? basename($path);
        $dir = dirname($path);
        $tmp = $path . '.optimizing.pdf';

        $oldSize = (int) @filesize($path);
        $oldPages = $this->pageCount($path);
        if ($oldSize <= 0 || $oldPages <= 0) {
            return $name;
        }

        $engine = strtolower((string) config('kanban.pdf_optimization.engine', 'auto'));
        $ok = false;

        if ($engine === 'auto' || $engine === 'ghostscript') {
            $gs = $this->ghostscriptBinary();
            if ($gs !== null) {
                $ok = $this->runGhostscript($gs, $path, $tmp);
            }
        }

        if (! $ok && ($engine === 'auto' || $engine === 'qpdf')) {
            $qpdf = $this->qpdfBinary();
            if ($qpdf !== null) {
                $ok = $this->runQpdf($qpdf, $path, $tmp);
            }
        }

        // Fallback murni PHP: recompress FlateDecode streams (level 9)
        // dan kompres stream yang belum terkompresi. Tidak butuh binary eksternal.
        if (! $ok && $engine === 'auto') {
            $ok = $this->runPhpOptimization($path, $tmp);
        }

        if (! $ok || ! is_file($tmp)) {
            @unlink($tmp);
            try {
                logger()->info('KanbanPdfOptimizer result', [
                    'file' => $name,
                    'status' => 'skipped-no-engine-or-failed',
                    'before' => $oldSize,
                    'after' => $oldSize,
                    'pages' => $oldPages,
                ]);
            } catch (\Throwable $ignored) {
            }
            return $name;
        }

        $newSize = (int) @filesize($tmp);
        $newPages = $this->pageCount($tmp);

        $onlyIfSmaller = filter_var(config('kanban.pdf_optimization.only_if_smaller', true), FILTER_VALIDATE_BOOLEAN);
        $valid = $newPages === $oldPages && $newPages > 0 && $newSize > 0;
        $smaller = ! $onlyIfSmaller || $newSize < $oldSize;

        if ($valid && $smaller) {
            if (@copy($tmp, $path)) {
                @unlink($tmp);
                try {
                    logger()->info('KanbanPdfOptimizer OK', [
                        'file' => $name,
                        'engine' => $engine,
                        'before' => $oldSize,
                        'after' => $newSize,
                        'pages' => $newPages,
                    ]);
                } catch (\Throwable $ignored) {
                }
                return $name;
            }
        }

        @unlink($tmp);
        try {
            logger()->info('KanbanPdfOptimizer skip', [
                'file' => $name,
                'valid' => $valid,
                'smaller' => $smaller,
                'before' => $oldSize,
                'after' => $newSize,
            ]);
        } catch (\Throwable $ignored) {
        }

        return $name;
    }

    protected function enabled(): bool
    {
        return filter_var(config('kanban.pdf_optimization.enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    protected function pageCount(string $path): int
    {
        try {
            $pdf = new Fpdi();
            return (int) $pdf->setSourceFile($path);
        } catch (\Throwable $e) {
            return -1;
        }
    }

    protected function detectBinaries(): void
    {
        if ($this->detected) {
            return;
        }
        $this->detected = true;

        $this->ghostscript = $this->findBinary(['gswin64c', 'gswin32c', 'gs']);
        $this->qpdf = $this->findBinary(['qpdf']);
    }

    protected function ghostscriptBinary(): ?string
    {
        $this->detectBinaries();
        return $this->ghostscript;
    }

    protected function qpdfBinary(): ?string
    {
        $this->detectBinaries();
        return $this->qpdf;
    }

    protected function findBinary(array $candidates): ?string
    {
        if (! function_exists('exec')) {
            return null;
        }

        foreach ($candidates as $bin) {
            $cmd = escapeshellarg($bin) . ' --version';
            $output = [];
            $rc = 1;
            $nullDevice = (PHP_OS_FAMILY === 'Windows') ? 'NUL' : '/dev/null';
            @exec($cmd . ' 2>' . $nullDevice, $output, $rc);
            if ($rc === 0) {
                return $bin;
            }
        }

        return null;
    }

    protected function runGhostscript(string $gs, string $input, string $output): bool
    {
        if (! function_exists('exec')) {
            return false;
        }

        $dpi = max(50, (int) config('kanban.pdf_optimization.dpi', 150));
        $timeout = max(10, (int) config('kanban.pdf_optimization.timeout', 120));

        $cmd = implode(' ', [
            escapeshellarg($gs),
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-dPDFSETTINGS=/ebook',
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            '-dDetectDuplicateImages=true',
            '-dCompressFonts=true',
            '-dSubsetFonts=true',
            '-dDownsampleColorImages=true',
            '-dDownsampleGrayImages=true',
            '-dDownsampleMonoImages=true',
            '-dColorImageResolution=' . $dpi,
            '-dGrayImageResolution=' . $dpi,
            '-dMonoImageResolution=' . $dpi,
            '-sOutputFile=' . escapeshellarg($output),
            escapeshellarg($input),
        ]);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = @proc_open($cmd, $descriptors, $pipes);
        if (! is_resource($proc)) {
            return false;
        }

        @fclose($pipes[0]);
        @stream_set_blocking($pipes[1], false);
        @stream_set_blocking($pipes[2], false);

        $start = time();
        $status = proc_get_status($proc);
        while ($status['running']) {
            if (time() - $start >= $timeout) {
                @proc_terminate($proc);
                @proc_close($proc);
                @unlink($output);
                return false;
            }
            usleep(200000);
            $status = proc_get_status($proc);
        }

        @fclose($pipes[1]);
        @fclose($pipes[2]);
        $code = proc_close($proc);

        return $code === 0 && is_file($output);
    }

    protected function runQpdf(string $qpdf, string $input, string $output): bool
    {
        if (! function_exists('exec')) {
            return false;
        }

        $cmd = implode(' ', [
            escapeshellarg($qpdf),
            '--compress-streams=y',
            '--object-streams=generate',
            '--recompress-flate',
            '--compression-level=9',
            escapeshellarg($input),
            escapeshellarg($output),
        ]);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = @proc_open($cmd, $descriptors, $pipes);
        if (! is_resource($proc)) {
            return false;
        }

        @fclose($pipes[0]);
        @stream_set_blocking($pipes[1], false);
        @stream_set_blocking($pipes[2], false);

        $timeout = max(10, (int) config('kanban.pdf_optimization.timeout', 120));
        $start = time();
        $status = proc_get_status($proc);
        while ($status['running']) {
            if (time() - $start >= $timeout) {
                @proc_terminate($proc);
                @proc_close($proc);
                @unlink($output);
                return false;
            }
            usleep(200000);
        }

        @fclose($pipes[1]);
        @fclose($pipes[2]);
        $code = proc_close($proc);

        return $code === 0 && is_file($output);
    }

    protected function runPhpOptimization(string $input, string $output): bool
    {
        $data = @file_get_contents($input);
        if (! is_string($data) || $data === '') {
            return false;
        }

        $changed = false;
        $matchCount = 0;
        $compressedCount = 0;
        $pattern = '/(<<[^<>]*(?:<<[^<>]*>>[^<>]*)*>>[ \\t]*\\r?\\nstream\\r?\\n)(.*?)(\\r?\\nendstream)/s';

        $result = preg_replace_callback($pattern, static function (array $m) use (&$changed, &$matchCount, &$compressedCount): string {
            $dict = $m[1];
            $stream = $m[2];
            $tail = $m[3];

            $matchCount++;

            $isFlate = (stripos($dict, '/FlateDecode') !== false)
                || (stripos($dict, '/Filter/FlateDecode') !== false);

            if ($isFlate) {
                $raw = @gzuncompress($stream);
                if (! is_string($raw)) {
                    return $m[0];
                }
            } else {
                // Jangan sentuh stream yang punya filter lain.
                if (preg_match('/\\/Filter\\s*\\//', $dict)) {
                    return $m[0];
                }
                $raw = $stream;
            }

            $compressed = @gzcompress($raw, 9);
            if (! is_string($compressed) || strlen($compressed) >= strlen($stream)) {
                return $m[0];
            }

            $newDict = $dict;
            if (preg_match('/\\/Length\\s+\\d+/', $newDict)) {
                $newDict = preg_replace('/\\/Length\\s+\\d+/', '/Length ' . strlen($compressed), $newDict);
            } else {
                $newDict = str_replace('>>', '/Length ' . strlen($compressed) . ' >>', $newDict);
            }

            if (! $isFlate) {
                $newDict = str_replace('>>', '/Filter /FlateDecode >>', $newDict);
            }

            $changed = true;
            $compressedCount++;
            return $newDict . $compressed . $tail;
        }, $data);

        if (! is_string($result) || ! $changed) {
            try {
                logger()->info('KanbanPdfOptimizer php', [
                    'matches' => $matchCount,
                    'compressed' => $compressedCount,
                    'changed' => $changed,
                    'before' => strlen($data),
                    'after' => is_string($result) ? strlen($result) : null,
                ]);
            } catch (\Throwable $ignored) {
            }
            return false;
        }

        // Perbaiki tabel xref karena panjang stream berubah.
        $rebuilt = $this->rebuildPdfXref($result);
        if (! is_string($rebuilt)) {
            return false;
        }
        $result = $rebuilt;

        if (@file_put_contents($output, $result) === false) {
            return false;
        }

        try {
            logger()->info('KanbanPdfOptimizer php', [
                'matches' => $matchCount,
                'compressed' => $compressedCount,
                'changed' => $changed,
                'before' => strlen($data),
                'after' => strlen($result),
            ]);
        } catch (\Throwable $ignored) {
        }

        return true;
    }

    protected function rebuildPdfXref(string $pdf): ?string
    {
        if (! preg_match('/\\/Root\\s+(\\d+\\s+\\d+\\s+R)/', $pdf, $rootMatch)) {
            return null;
        }
        $root = $rootMatch[1];

        $size = 0;
        if (preg_match('/\\/Size\\s+(\\d+)/', $pdf, $sizeMatch)) {
            $size = (int) $sizeMatch[1];
        }

        preg_match_all('/(\\d+)\\s+(\\d+)\\s+obj\\b/', $pdf, $objMatches, PREG_OFFSET_CAPTURE);
        $offsets = [];
        $maxObj = 0;
        if (! empty($objMatches[0])) {
            foreach ($objMatches[0] as $match) {
                // match[0] like "1 0 obj", match[1] offset
                if (! preg_match('/^(\\d+)\\s+(\\d+)\\s+obj$/', trim($match[0]), $parts)) {
                    continue;
                }
                $num = (int) $parts[1];
                $off = (int) $match[1];
                $offsets[$num] = $off;
                if ($num > $maxObj) {
                    $maxObj = $num;
                }
            }
        }

        if ($size <= 0 || $size <= $maxObj) {
            $size = $maxObj + 1;
        }
        if ($size < 2) {
            return null;
        }

        $xrefPos = strrpos($pdf, 'xref');
        if ($xrefPos === false) {
            return null;
        }

        $body = rtrim(substr($pdf, 0, $xrefPos));
        $body .= "\n";

        $xref = "xref\n";
        $xref .= "0 " . $size . "\n";
        $xref .= "0000000000 65535 f \n";
        for ($i = 1; $i < $size; $i++) {
            if (isset($offsets[$i])) {
                $xref .= sprintf("%010d %05d n \n", $offsets[$i], 0);
            } else {
                $xref .= "0000000000 65535 f \n";
            }
        }

        $xrefOffset = strlen($body);
        $trailer = "trailer\n<< /Size " . $size . " /Root " . $root . " >>\n";
        $trailer .= "startxref\n" . $xrefOffset . "\n%%EOF\n";

        return $body . $xref . $trailer;
    }
}

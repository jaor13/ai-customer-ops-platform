<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Extracts plain text from an uploaded document so n8n stays a pure
 * orchestrator (chunk → embed → store). Handles txt/md/docx/pdf, with an
 * OCR fallback for scanned PDFs via ocrmypdf. See docs/12 §5.
 */
class DocumentTextExtractor
{
    /**
     * Minimum extracted length below which a PDF is treated as scanned
     * (image-only) and routed through OCR.
     */
    private const SCANNED_PDF_THRESHOLD = 100;

    /**
     * @return array{text: string, source_type: string, page_count: ?int, ocr_used: bool}
     */
    public function extract(string $localPath, string $extension): array
    {
        return match (strtolower($extension)) {
            'txt' => $this->fromPlainText($localPath, 'text'),
            'md' => $this->fromPlainText($localPath, 'md'),
            'docx' => $this->fromDocx($localPath),
            'pdf' => $this->fromPdf($localPath),
            default => ['text' => '', 'source_type' => 'text', 'page_count' => null, 'ocr_used' => false],
        };
    }

    /**
     * @return array{text: string, source_type: string, page_count: ?int, ocr_used: bool}
     */
    private function fromPlainText(string $path, string $sourceType): array
    {
        return [
            'text' => $this->clean((string) file_get_contents($path)),
            'source_type' => $sourceType,
            'page_count' => null,
            'ocr_used' => false,
        ];
    }

    /**
     * @return array{text: string, source_type: string, page_count: ?int, ocr_used: bool}
     */
    private function fromDocx(string $path): array
    {
        $phpWord = WordIOFactory::load($path);
        $parts = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $parts[] = $this->elementText($element);
            }
        }

        return [
            'text' => $this->clean(implode("\n", array_filter($parts))),
            'source_type' => 'docx',
            'page_count' => null,
            'ocr_used' => false,
        ];
    }

    /**
     * Recursively pull text out of a PhpWord element tree.
     */
    private function elementText(object $element): string
    {
        if (method_exists($element, 'getText')) {
            $text = $element->getText();

            return is_string($text) ? $text : '';
        }

        if (method_exists($element, 'getElements')) {
            $parts = [];
            foreach ($element->getElements() as $child) {
                $parts[] = $this->elementText($child);
            }

            return implode('', $parts);
        }

        return '';
    }

    /**
     * @return array{text: string, source_type: string, page_count: ?int, ocr_used: bool}
     */
    private function fromPdf(string $path): array
    {
        $parser = new PdfParser;
        $pdf = $parser->parseFile($path);
        $text = $this->clean($pdf->getText());
        $pageCount = count($pdf->getPages());

        // Digital PDF with a usable text layer.
        if (mb_strlen($text) >= self::SCANNED_PDF_THRESHOLD) {
            return ['text' => $text, 'source_type' => 'pdf', 'page_count' => $pageCount, 'ocr_used' => false];
        }

        // Likely scanned/image-only — try OCR if enabled and available.
        $ocrText = $this->ocrPdf($path);

        if ($ocrText !== null && mb_strlen($ocrText) > mb_strlen($text)) {
            return ['text' => $ocrText, 'source_type' => 'pdf_ocr', 'page_count' => $pageCount, 'ocr_used' => true];
        }

        return ['text' => $text, 'source_type' => 'pdf', 'page_count' => $pageCount, 'ocr_used' => false];
    }

    /**
     * Run ocrmypdf to add a text layer, then re-extract. Returns null if OCR
     * is disabled or the tooling is unavailable (degrade gracefully).
     */
    private function ocrPdf(string $path): ?string
    {
        if (! config('services.knowledge_base.ocr_enabled', true)) {
            return null;
        }

        $binary = config('services.knowledge_base.ocrmypdf_path', 'ocrmypdf');
        $outputPath = tempnam(sys_get_temp_dir(), 'kb_ocr_').'.pdf';

        try {
            $result = Process::timeout(300)->run([
                $binary,
                '--skip-text',
                '--quiet',
                $path,
                $outputPath,
            ]);

            if (! $result->successful()) {
                Log::warning('ocrmypdf failed', ['error' => $result->errorOutput()]);

                return null;
            }

            $text = $this->clean((new PdfParser)->parseFile($outputPath)->getText());

            return $text;
        } catch (\Throwable $e) {
            Log::warning('OCR unavailable or errored', ['message' => $e->getMessage()]);

            return null;
        } finally {
            if (is_file($outputPath)) {
                @unlink($outputPath);
            }
        }
    }

    /**
     * Normalize whitespace without destroying paragraph structure.
     */
    private function clean(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);          // collapse runs of spaces/tabs
        $text = preg_replace('/\n{3,}/', "\n\n", $text);       // cap blank-line runs

        return trim($text);
    }
}

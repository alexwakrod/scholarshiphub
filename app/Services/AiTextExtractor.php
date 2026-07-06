<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory;


class AiTextExtractor
{
    /**
     * Extract text from an uploaded file by its real (temporary) path.
     *
     * @param  string $filePath   Full path to the file on disk
     * @param  string $extension  Lowercase file extension (pdf, docx, doc, txt, etc.)
     * @return string             Extracted text (may be empty if all methods fail)
     */
    public function extract(string $filePath, string $extension): string
    {
        $extension = strtolower($extension);

        try {
            if ($extension === 'pdf') {
                return $this->extractPdf($filePath);
            }

            if (in_array($extension, ['doc', 'docx'])) {
                return $this->extractWord($filePath);
            }

            if ($extension === 'txt') {
                return $this->extractPlainText($filePath);
            }

            // For unknown types, try plain text read as last resort
            $text = $this->extractPlainText($filePath);
            if (!empty(trim($text))) {
                return $text;
            }

            Log::warning('AiTextExtractor: unsupported file type', ['extension' => $extension]);
            return '';
        } catch (\Throwable $e) {
            Log::error('AiTextExtractor: extraction failed', [
                'file'      => $filePath,
                'extension' => $extension,
                'error'     => $e->getMessage(),
            ]);

            // Final fallback: try to read as plain text
            try {
                return $this->extractPlainText($filePath);
            } catch (\Throwable $fallbackError) {
                Log::error('AiTextExtractor: even plain text fallback failed', [
                    'error' => $fallbackError->getMessage(),
                ]);
                return '';
            }
        }
    }

    /**
     * Extract text from a PDF using Smalot\PdfParser with error recovery.
     */
    private function extractPdf(string $path): string
    {
        try {
            if (!file_exists($path) || !is_readable($path)) {
                Log::warning('AiTextExtractor: PDF file not readable', ['path' => $path]);
                return '';
            }

            $parser = new PdfParser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();

            // getText() can return null or empty
            if (!is_string($text) || trim($text) === '') {
                // Fallback: iterate pages
                $pages = $pdf->getPages();
                $text = '';
                foreach ($pages as $page) {
                    $text .= $page->getText() . "\n";
                }
            }

            $clean = trim($text);
            if (empty($clean)) {
                Log::warning('AiTextExtractor: PDF appears to contain no extractable text', ['path' => $path]);
                // Last resort: raw bytes cleaning
                $raw = file_get_contents($path);
                $raw = preg_replace('/[^\P{C}\n\r\t]/u', ' ', $raw);
                $raw = preg_replace('/\s+/', ' ', $raw);
                return strlen($raw) > 100 ? $raw : '';
            }

            return $clean;
        } catch (\Throwable $e) {
            Log::error('AiTextExtractor: PDF parsing threw exception', [
                'error' => $e->getMessage(),
                'path'  => $path,
            ]);
            // Raw byte fallback on exception
            try {
                $raw = file_get_contents($path);
                $raw = preg_replace('/[^\P{C}\n\r\t]/u', ' ', $raw);
                $raw = preg_replace('/\s+/', ' ', $raw);
                return strlen($raw) > 100 ? $raw : '';
            } catch (\Throwable $e2) {
                return '';
            }
        }
    }

    /**
     * Extract text from Word documents (docx, doc).
     */
    private function extractWord(string $path): string
    {
        try {
            if (!file_exists($path) || !is_readable($path)) {
                Log::warning('AiTextExtractor: Word file not readable', ['path' => $path]);
                return '';
            }

            $phpWord = IOFactory::load($path);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . ' ';
                    } elseif (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $child) {
                            if (method_exists($child, 'getText')) {
                                $text .= $child->getText() . ' ';
                            }
                        }
                    }
                }
            }

            $clean = trim($text);
            return $clean;
        } catch (\Throwable $e) {
            Log::error('AiTextExtractor: Word parsing failed', [
                'error' => $e->getMessage(),
                'path'  => $path,
            ]);
            // Try to read raw and clean
            try {
                $raw = file_get_contents($path);
                // For docx, it's a zip; we can try to extract XML parts if needed, but for now just clean
                $raw = preg_replace('/[^\P{C}\n\r\t]/u', ' ', $raw);
                $raw = preg_replace('/\s+/', ' ', $raw);
                return strlen($raw) > 100 ? $raw : '';
            } catch (\Throwable $e2) {
                return '';
            }
        }
    }

    /**
     * Read a plain text file with encoding detection.
     */
    private function extractPlainText(string $path): string
    {
        try {
            if (!file_exists($path) || !is_readable($path)) {
                Log::warning('AiTextExtractor: plain text file not readable', ['path' => $path]);
                return '';
            }

            $content = file_get_contents($path);
            if ($content === false) {
                return '';
            }

            // Attempt to detect encoding and convert to UTF-8
            $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            }

            // Remove BOM
            if (str_starts_with($content, "\xEF\xBB\xBF")) {
                $content = substr($content, 3);
            }

            return trim($content);
        } catch (\Throwable $e) {
            Log::error('AiTextExtractor: plain text read failed', ['error' => $e->getMessage()]);
            return '';
        }
    }
}
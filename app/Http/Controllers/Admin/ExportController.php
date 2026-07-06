<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Scholarship;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Export scholarships to the requested format.
     * Supported formats: csv, xlsx, pdf
     */
    public function export(Request $request)
    {
        try {
            $request->validate([
                'format' => 'required|in:csv,xlsx,pdf',
                'fields' => 'nullable|array',
                'fields.*' => 'string|in:id,title,provider,country,amount,deadline,status',
                'search' => 'nullable|string',
                'status' => 'nullable|in:active,expired',
            ]);

            $format = $request->input('format', 'csv');
            $fields = $request->input('fields', ['id', 'title', 'provider', 'country', 'deadline', 'status']);

            $query = Scholarship::query();

            if ($search = $request->input('search')) {
                $query->whereRaw("search_vector @@ plainto_tsquery('english', ?)", [$search]);
            }
            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }

            $scholarships = $query->get($fields);

            $filename = 'scholarships_' . now()->format('Y-m-d_His') . '.' . $format;

            return match ($format) {
                'csv'   => $this->exportCsv($scholarships, $fields, $filename),
                'xlsx'  => $this->exportXlsx($scholarships, $fields, $filename),
                'pdf'   => $this->exportPdf($scholarships, $fields, $filename),
                default => abort(400, 'Unsupported format.'),
            };
        } catch (\Throwable $e) {
            Log::error('ExportController error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Export failed.');
        }
    }

    private function exportCsv($data, $fields, $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $col = 'A';
        foreach ($fields as $field) {
            $sheet->setCellValue($col . '1', ucfirst($field));
            $col++;
        }

        // Data
        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($fields as $field) {
                $value = $item->$field;
                if ($field === 'deadline' && $value) {
                    $value = $value instanceof \DateTime ? $value->format('Y-m-d') : $value;
                }
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        $writer = new Csv($spreadsheet);
        $temp = tempnam(sys_get_temp_dir(), 'csv');
        $writer->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'text/csv',
        ])->deleteFileAfterSend(true);
    }

    private function exportXlsx($data, $fields, $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $col = 'A';
        foreach ($fields as $field) {
            $sheet->setCellValue($col . '1', ucfirst($field));
            $col++;
        }

        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($fields as $field) {
                $value = $item->$field;
                if ($field === 'deadline' && $value) {
                    $value = $value instanceof \DateTime ? $value->format('Y-m-d') : $value;
                }
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $temp = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function exportPdf($data, $fields, $filename)
    {
        $html = '<h1>Scholarships Export</h1><table border="1" cellpadding="5" cellspacing="0" style="width:100%;border-collapse:collapse;">';
        $html .= '<tr>';
        foreach ($fields as $field) {
            $html .= '<th>' . ucfirst($field) . '</th>';
        }
        $html .= '</tr>';

        foreach ($data as $item) {
            $html .= '<tr>';
            foreach ($fields as $field) {
                $value = $item->$field;
                if ($field === 'deadline' && $value) {
                    $value = $value instanceof \DateTime ? $value->format('Y-m-d') : $value;
                }
                $html .= '<td>' . e($value) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';

        $pdf = Pdf::loadHTML($html);
        return $pdf->download($filename);
    }
}
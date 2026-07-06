<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Barryvdh\DomPDF\Facade\Pdf;

class UserExportController extends Controller
{
    /**
     * Export users to CSV, XLSX, or PDF.
     */
    public function export(Request $request)
    {
        try {
            $request->validate([
                'format' => 'required|in:csv,xlsx,pdf',
                'fields' => 'nullable|array',
                'fields.*' => 'string|in:id,name,email,role,created_at',
                'search' => 'nullable|string',
                'role' => 'nullable|in:student,admin',
            ]);

            $format = $request->input('format', 'csv');
            $fields = $request->input('fields', ['id', 'name', 'email', 'role', 'created_at']);

            $query = User::query();

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('email', 'ILIKE', "%{$search}%");
                });
            }
            if ($role = $request->input('role')) {
                $query->where('role', $role);
            }

            $users = $query->get($fields);

            $filename = 'users_' . now()->format('Y-m-d_His') . '.' . $format;

            return match ($format) {
                'csv'   => $this->exportCsv($users, $fields, $filename),
                'xlsx'  => $this->exportXlsx($users, $fields, $filename),
                'pdf'   => $this->exportPdf($users, $fields, $filename),
                default => abort(400, 'Unsupported format.'),
            };
        } catch (\Throwable $e) {
            Log::error('UserExportController error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Export failed.');
        }
    }

    private function exportCsv($data, $fields, $filename)
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
                if ($field === 'created_at' && $value) {
                    $value = $value instanceof \DateTime ? $value->format('Y-m-d H:i:s') : $value;
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
                if ($field === 'created_at' && $value) {
                    $value = $value instanceof \DateTime ? $value->format('Y-m-d H:i:s') : $value;
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
        $html = '<h1>Users Export</h1><table border="1" cellpadding="5" cellspacing="0" style="width:100%;border-collapse:collapse;">';
        $html .= '<tr>';
        foreach ($fields as $field) {
            $html .= '<th>' . ucfirst($field) . '</th>';
        }
        $html .= '</tr>';

        foreach ($data as $item) {
            $html .= '<tr>';
            foreach ($fields as $field) {
                $value = $item->$field;
                if ($field === 'created_at' && $value) {
                    $value = $value instanceof \DateTime ? $value->format('Y-m-d H:i:s') : $value;
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
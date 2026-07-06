<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\ImportJob;
use App\Models\Scholarship;

class ImportScholarshipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 2;

    private int $importJobId;

    public function __construct(int $importJobId)
    {
        $this->importJobId = $importJobId;
        $this->onQueue('import');
    }

    public function handle(): void
    {
        $importJob = null;
        try {
            $importJob = ImportJob::find($this->importJobId);
            if (!$importJob) {
                Log::error('ImportJob not found: ' . $this->importJobId);
                return;
            }

            $path = $importJob->file_path;
            if (!Storage::disk('local')->exists($path)) {
                throw new \Exception('Import file missing: ' . $path);
            }

            $importJob->update([
                'status'     => 'processing',
                'started_at' => now(),
            ]);

            $fileStream = Storage::disk('local')->readStream($path);
            if (!$fileStream) {
                throw new \Exception('Unable to open file: ' . $path);
            }

            $csvHeader = fgetcsv($fileStream);
            if (!$csvHeader) {
                throw new \Exception('CSV file is empty or missing header row.');
            }

            // Read the mapping: target_field => csv column name
            $mapping = $importJob->column_mapping ?? [];
            // Build an index: csv column index -> target field
            $columnIndexMap = [];
            foreach ($mapping as $targetField => $csvColumn) {
                $idx = array_search($csvColumn, $csvHeader);
                if ($idx !== false) {
                    $columnIndexMap[$idx] = $targetField;
                }
            }

            if (empty($columnIndexMap)) {
                throw new \Exception('No valid column mapping found.');
            }

            // Count total rows
            $totalRows = 0;
            while (fgetcsv($fileStream) !== false) {
                $totalRows++;
            }
            rewind($fileStream);
            fgetcsv($fileStream); // skip header again

            $importJob->update(['total_rows' => $totalRows]);

            $processed = 0;
            $failed = 0;
            $errors = [];
            $chunkSize = config('scholarship.import.chunk_size', 100);
            $batch = [];

            while (($row = fgetcsv($fileStream)) !== false) {
                try {
                    $mappedRow = [];
                    foreach ($columnIndexMap as $csvIdx => $targetField) {
                        $mappedRow[$targetField] = $row[$csvIdx] ?? null;
                    }

                    // Ensure required fields are present
                    $required = ['title', 'description', 'provider', 'country', 'deadline'];
                    foreach ($required as $field) {
                        if (empty($mappedRow[$field])) {
                            throw new \Exception("Required field '{$field}' is empty.");
                        }
                    }

                    $batch[] = [
                        'title'             => trim($mappedRow['title']),
                        'description'       => trim($mappedRow['description']),
                        'provider'          => trim($mappedRow['provider']),
                        'country'           => trim($mappedRow['country']),
                        'amount'            => !empty($mappedRow['amount']) ? floatval($mappedRow['amount']) : null,
                        'deadline'          => \Carbon\Carbon::parse($mappedRow['deadline'])->toDateTimeString(),
                        'source_url'        => trim($mappedRow['source_url'] ?? ''),
                        'status'            => 'active',
                        'parsed_requirements' => null,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];

                    if (count($batch) >= $chunkSize) {
                        Scholarship::insert($batch);
                        $processed += count($batch);
                        $batch = [];
                        $importJob->update(['processed_rows' => $processed]);
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = 'Row error: ' . $e->getMessage();
                }
            }

            // Insert remaining
            if (count($batch) > 0) {
                Scholarship::insert($batch);
                $processed += count($batch);
                $importJob->update(['processed_rows' => $processed]);
            }

            fclose($fileStream);

            $importJob->update([
                'status'         => $failed > 0 ? 'completed' : 'completed',
                'processed_rows' => $processed,
                'failed_rows'    => $failed,
                'error_messages' => $errors,
                'finished_at'    => now(),
            ]);

            Log::info('Import completed.', [
                'import_job_id' => $this->importJobId,
                'processed'     => $processed,
                'failed'        => $failed,
            ]);
        } catch (\Throwable $e) {
            Log::error('ImportScholarshipsJob failed: ' . $e->getMessage(), [
                'import_job_id' => $this->importJobId,
                'trace'         => $e->getTraceAsString(),
            ]);

            if ($importJob) {
                $importJob->update([
                    'status'      => 'failed',
                    'finished_at' => now(),
                    'error_messages' => array_merge(
                        $importJob->error_messages ?? [],
                        [$e->getMessage()]
                    ),
                ]);
            }

            throw $e;
        } finally {
            if (isset($fileStream) && is_resource($fileStream)) {
                fclose($fileStream);
            }
        }
    }
}
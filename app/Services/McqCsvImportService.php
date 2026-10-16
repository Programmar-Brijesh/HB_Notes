<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\McqQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class McqCsvImportService
{
    /**
     * Exact required column headers.
     */
    public const REQUIRED_HEADERS = [
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'explanation',
        'difficulty',
        'status',
    ];

    /**
     * Parse and validate CSV without saving to database (Preview Phase).
     */
    public function parseAndValidate(string $fullPath, int $chapterId): array
    {
        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            return [
                'success' => false,
                'error' => 'The uploaded CSV file could not be read from storage.',
            ];
        }

        $handle = fopen($fullPath, 'r');
        if (!$handle) {
            return [
                'success' => false,
                'error' => 'Failed to open the uploaded CSV file for reading.',
            ];
        }

        // Read and validate headers
        $rawHeaderLine = fgetcsv($handle);
        if (!$rawHeaderLine) {
            fclose($handle);
            return [
                'success' => false,
                'error' => 'The uploaded file is empty or does not contain a valid CSV header row.',
            ];
        }

        // Handle UTF-8 BOM on first column
        if (isset($rawHeaderLine[0])) {
            $rawHeaderLine[0] = preg_replace('/^\xEF\xBB\xBF/', '', $rawHeaderLine[0]);
        }

        $headers = array_map(fn($h) => strtolower(trim((string) $h)), $rawHeaderLine);

        // Verify headers match expected format
        $missingHeaders = array_diff(self::REQUIRED_HEADERS, $headers);
        if (!empty($missingHeaders)) {
            fclose($handle);
            return [
                'success' => false,
                'error' => 'Invalid CSV headers. Missing required columns: ' . implode(', ', $missingHeaders) . '. Expected: ' . implode(',', self::REQUIRED_HEADERS),
            ];
        }

        $maxRows = config('hbnotes.mcq_max_import_rows', 1000);
        $existingHashes = McqQuestion::where('chapter_id', $chapterId)
            ->pluck('question_hash')
            ->flip()
            ->all();

        $seenInCsvHashes = [];
        $totalDataRows = 0;
        $validRows = [];
        $invalidRows = [];
        $duplicateRows = [];
        $previewRows = [];

        $rowNumber = 1; // 1-indexed, header is row 1

        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip empty rows
            if (count($data) === 1 && trim((string) $data[0]) === '') {
                continue;
            }

            $totalDataRows++;

            if ($totalDataRows > $maxRows) {
                fclose($handle);
                return [
                    'success' => false,
                    'error' => "The uploaded CSV contains more than the maximum permitted {$maxRows} rows. Please split your file into smaller batches.",
                ];
            }

            // Check column count
            if (count($data) !== count(self::REQUIRED_HEADERS)) {
                $invalidRows[] = [
                    'row' => $rowNumber,
                    'question' => trim((string) ($data[0] ?? 'N/A')),
                    'reason' => 'Row has ' . count($data) . ' columns, expected ' . count(self::REQUIRED_HEADERS) . ' columns.',
                ];
                continue;
            }

            $row = array_combine($headers, $data);

            // Validate row contents
            $validationResult = $this->validateRow($row, $rowNumber);
            if (!$validationResult['valid']) {
                $invalidRows[] = [
                    'row' => $rowNumber,
                    'question' => trim((string) ($row['question'] ?? 'N/A')),
                    'reason' => $validationResult['reason'],
                ];
                continue;
            }

            $normalizedRow = $validationResult['data'];
            $hash = McqQuestion::computeQuestionHash($normalizedRow['question']);

            // Duplicate Check 1: Within the current CSV file
            if (isset($seenInCsvHashes[$hash])) {
                $duplicateRows[] = [
                    'row' => $rowNumber,
                    'question' => $normalizedRow['question'],
                    'reason' => "Duplicate question within this CSV file (previously seen at row {$seenInCsvHashes[$hash]}).",
                ];
                continue;
            }

            // Duplicate Check 2: Against existing database questions in this chapter
            if (isset($existingHashes[$hash])) {
                $duplicateRows[] = [
                    'row' => $rowNumber,
                    'question' => $normalizedRow['question'],
                    'reason' => 'Duplicate question already exists in this chapter in Question Bank.',
                ];
                continue;
            }

            // Mark hash as seen in this CSV
            $seenInCsvHashes[$hash] = $rowNumber;

            // Valid row
            $normalizedRow['row_number'] = $rowNumber;
            $normalizedRow['question_hash'] = $hash;
            $validRows[] = $normalizedRow;

            // Collect preview rows (first 10 valid)
            if (count($previewRows) < 10) {
                $previewRows[] = $normalizedRow;
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'total_rows' => $totalDataRows,
            'valid_count' => count($validRows),
            'invalid_count' => count($invalidRows),
            'duplicate_count' => count($duplicateRows),
            'invalid_rows' => $invalidRows,
            'duplicate_rows' => $duplicateRows,
            'preview_rows' => $previewRows,
        ];
    }

    /**
     * Validate an individual row's values.
     */
    public function validateRow(array $row, int $rowNumber): array
    {
        $question = trim((string) ($row['question'] ?? ''));
        $optA = trim((string) ($row['option_a'] ?? ''));
        $optB = trim((string) ($row['option_b'] ?? ''));
        $optC = trim((string) ($row['option_c'] ?? ''));
        $optD = trim((string) ($row['option_d'] ?? ''));
        $correct = strtoupper(trim((string) ($row['correct_option'] ?? '')));
        $explanation = trim((string) ($row['explanation'] ?? ''));
        $difficulty = strtolower(trim((string) ($row['difficulty'] ?? 'medium')));
        $status = strtolower(trim((string) ($row['status'] ?? 'published')));

        if ($question === '') {
            return ['valid' => false, 'reason' => 'Question text cannot be empty.'];
        }

        if (mb_strlen($question, 'UTF-8') > 5000) {
            return ['valid' => false, 'reason' => 'Question text exceeds 5,000 characters limit.'];
        }

        if ($optA === '' || $optB === '' || $optC === '' || $optD === '') {
            return ['valid' => false, 'reason' => 'All four options (option_a, option_b, option_c, option_d) must be non-empty.'];
        }

        if (mb_strlen($optA, 'UTF-8') > 2000 || mb_strlen($optB, 'UTF-8') > 2000 || mb_strlen($optC, 'UTF-8') > 2000 || mb_strlen($optD, 'UTF-8') > 2000) {
            return ['valid' => false, 'reason' => 'Option text exceeds 2,000 characters limit.'];
        }

        if (!in_array($correct, ['A', 'B', 'C', 'D'], true)) {
            return ['valid' => false, 'reason' => "Invalid correct option '{$row['correct_option']}'. Must be A, B, C, or D."];
        }

        if (!in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            return ['valid' => false, 'reason' => "Invalid difficulty '{$row['difficulty']}'. Must be easy, medium, or hard."];
        }

        if (!in_array($status, ['draft', 'published'], true)) {
            return ['valid' => false, 'reason' => "Invalid status '{$row['status']}'. Must be draft or published."];
        }

        if ($explanation !== '' && mb_strlen($explanation, 'UTF-8') > 5000) {
            return ['valid' => false, 'reason' => 'Explanation exceeds 5,000 characters limit.'];
        }

        return [
            'valid' => true,
            'data' => [
                'question' => $question,
                'option_a' => $optA,
                'option_b' => $optB,
                'option_c' => $optC,
                'option_d' => $optD,
                'correct_option' => $correct,
                'explanation' => $explanation !== '' ? $explanation : null,
                'difficulty' => $difficulty,
                'status' => $status,
            ],
        ];
    }

    /**
     * Commit valid questions in chunks/batches inside database transactions.
     */
    public function commitImport(string $fullPath, int $chapterId): array
    {
        $parsed = $this->parseAndValidate($fullPath, $chapterId);
        if (!$parsed['success']) {
            return [
                'success' => false,
                'error' => $parsed['error'],
            ];
        }

        $allErrors = array_merge($parsed['invalid_rows'], $parsed['duplicate_rows']);
        $batchSize = config('hbnotes.mcq_import_batch_size', 100);

        // Re-read file to stream batches into DB cleanly
        $handle = fopen($fullPath, 'r');
        $rawHeaderLine = fgetcsv($handle);
        if (isset($rawHeaderLine[0])) {
            $rawHeaderLine[0] = preg_replace('/^\xEF\xBB\xBF/', '', $rawHeaderLine[0]);
        }
        $headers = array_map(fn($h) => strtolower(trim((string) $h)), $rawHeaderLine);

        // Cache existing and seen hashes
        $existingHashes = McqQuestion::where('chapter_id', $chapterId)
            ->pluck('question_hash')
            ->flip()
            ->all();

        $seenInCsvHashes = [];
        $batch = [];
        $importedCount = 0;
        $failedCount = 0;
        $rowNumber = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count($data) === 1 && trim((string) $data[0]) === '') {
                continue;
            }

            if (count($data) !== count(self::REQUIRED_HEADERS)) {
                continue; // Already collected in invalid_rows
            }

            $row = array_combine($headers, $data);
            $validation = $this->validateRow($row, $rowNumber);
            if (!$validation['valid']) {
                continue; // Already collected in invalid_rows
            }

            $normalizedRow = $validation['data'];
            $hash = McqQuestion::computeQuestionHash($normalizedRow['question']);

            if (isset($seenInCsvHashes[$hash]) || isset($existingHashes[$hash])) {
                continue; // Already collected in duplicate_rows
            }

            $seenInCsvHashes[$hash] = $rowNumber;
            $existingHashes[$hash] = true;

            $batch[] = [
                'chapter_id' => $chapterId,
                'question' => $normalizedRow['question'],
                'question_hash' => $hash,
                'option_a' => $normalizedRow['option_a'],
                'option_b' => $normalizedRow['option_b'],
                'option_c' => $normalizedRow['option_c'],
                'option_d' => $normalizedRow['option_d'],
                'correct_option' => $normalizedRow['correct_option'],
                'explanation' => $normalizedRow['explanation'],
                'difficulty' => $normalizedRow['difficulty'],
                'status' => $normalizedRow['status'],
                'is_active' => true,
                'order_column' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                $batchResult = $this->insertBatch($batch);
                $importedCount += $batchResult['imported'];
                $failedCount += $batchResult['failed'];
                if (!empty($batchResult['errors'])) {
                    $allErrors = array_merge($allErrors, $batchResult['errors']);
                }
                $batch = [];
            }
        }

        // Process remaining rows in final batch
        if (!empty($batch)) {
            $batchResult = $this->insertBatch($batch);
            $importedCount += $batchResult['imported'];
            $failedCount += $batchResult['failed'];
            if (!empty($batchResult['errors'])) {
                $allErrors = array_merge($allErrors, $batchResult['errors']);
            }
        }

        fclose($handle);

        // Sort errors by row number
        usort($allErrors, fn($a, $b) => ($a['row'] ?? 0) <=> ($b['row'] ?? 0));

        $status = 'completed';
        if ($failedCount > 0 || !empty($allErrors)) {
            $status = $importedCount > 0 ? 'completed_with_errors' : 'failed';
        }

        return [
            'success' => true,
            'total_rows' => $parsed['total_rows'],
            'imported_count' => $importedCount,
            'duplicate_count' => $parsed['duplicate_count'],
            'invalid_count' => $parsed['invalid_count'],
            'failed_count' => $failedCount,
            'status' => $status,
            'errors' => $allErrors,
        ];
    }

    /**
     * Insert a batch inside a database transaction.
     */
    protected function insertBatch(array $records): array
    {
        DB::beginTransaction();
        try {
            McqQuestion::insert($records);
            DB::commit();
            return [
                'imported' => count($records),
                'failed' => 0,
                'errors' => [],
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('MCQ bulk import batch transaction failed: ' . $e->getMessage());

            $errors = [];
            foreach ($records as $item) {
                $errors[] = [
                    'row' => 0,
                    'question' => mb_substr($item['question'], 0, 80),
                    'reason' => 'Database batch transaction failed. Rolled back for data safety.',
                ];
            }

            return [
                'imported' => 0,
                'failed' => count($records),
                'errors' => $errors,
            ];
        }
    }

    /**
     * Generate standard CSV template content.
     */
    public function generateTemplate(): string
    {
        $headers = implode(',', self::REQUIRED_HEADERS);
        $sampleRows = [
            '"What is the SI unit of electric current?","Volt","Ampere","Ohm","Watt","B","Electric current is measured in amperes.","easy","published"',
            '"What is the HCF of 12 and 18?","2","3","6","9","C","6 is the greatest common divisor.","easy","draft"',
            '"पौधों में प्रकाश संश्लेषण के लिए क्या आवश्यक है?","क्लोरोफिल","सूर्य का प्रकाश","जल और कार्बन डाइऑक्साइड","उपरोक्त सभी","D","प्रकाश संश्लेषण के लिए सभी घटकों की आवश्यकता होती है।","medium","published"',
        ];

        return "\xEF\xBB\xBF" . $headers . "\r\n" . implode("\r\n", $sampleRows) . "\r\n";
    }

    /**
     * Generate an Error Report CSV with Formula Injection protection.
     */
    public function generateErrorCsv(array $errors): string
    {
        $output = fopen('php://temp', 'r+');

        // UTF-8 BOM
        fwrite($output, "\xEF\xBB\xBF");

        // Headers
        fputcsv($output, ['Row Number', 'Error Reason', 'Question Preview']);

        foreach ($errors as $err) {
            $rowNum = (string) ($err['row'] ?? '—');
            $reason = (string) ($err['reason'] ?? 'Unknown validation error');
            $question = (string) ($err['question'] ?? '');

            // Protect against CSV Formula Injection (characters: =, +, -, @, \t, \r)
            $safeReason = $this->sanitizeFormulaInjection($reason);
            $safeQuestion = $this->sanitizeFormulaInjection(mb_substr($question, 0, 200));

            fputcsv($output, [$rowNum, $safeReason, $safeQuestion]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Sanitize cell content against CSV/Excel formula injection.
     */
    protected function sanitizeFormulaInjection(string $value): string
    {
        $trimmed = ltrim($value);
        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }
        return $value;
    }
}

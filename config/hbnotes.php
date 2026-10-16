<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HB Notes File & Upload Configurations
    |--------------------------------------------------------------------------
    | Enforce safe file size limits and storage disks for academic materials.
    */
    'max_pdf_size_kb' => (int) env('MAX_PDF_SIZE_KB', 51200), // Default 50MB
    'max_thumbnail_size_kb' => (int) env('MAX_THUMBNAIL_SIZE_KB', 5120), // Default 5MB
    'allowed_pdf_mimes' => ['pdf'],
    'pdf_storage_disk' => env('PDF_STORAGE_DISK', 'local'),
    'pdf_storage_path' => 'notes/pdf',
    'thumbnail_storage_path' => 'notes/thumbnails',

    /*
    |--------------------------------------------------------------------------
    | MCQ Question Bank & Bulk Import Configurations
    |--------------------------------------------------------------------------
    */
    'mcq_max_csv_size_kb' => (int) env('MCQ_MAX_CSV_SIZE_KB', 5120), // 5MB default
    'mcq_max_import_rows' => (int) env('MCQ_MAX_IMPORT_ROWS', 1000), // Max 1,000 rows
    'mcq_import_batch_size' => (int) env('MCQ_IMPORT_BATCH_SIZE', 100), // 100 rows per batch
    'csv_temp_storage_path' => 'csv_imports',
];

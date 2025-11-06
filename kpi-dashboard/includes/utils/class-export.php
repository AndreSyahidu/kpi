<?php
/**
 * Export Utility
 */
class KPI_Dashboard_Export {

    /**
     * Export data to CSV
     */
    public static function to_csv($data, $filename = 'export.csv', $headers = []) {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/kpi-dashboard/exports';

        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }

        $filepath = $export_dir . '/' . sanitize_file_name($filename);
        $file = fopen($filepath, 'w');

        // Add BOM for UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Write headers
        if (!empty($headers)) {
            fputcsv($file, $headers);
        } elseif (!empty($data)) {
            // Auto-generate headers from first row
            $first_row = reset($data);
            if (is_object($first_row)) {
                $first_row = (array) $first_row;
            }
            fputcsv($file, array_keys($first_row));
        }

        // Write data
        foreach ($data as $row) {
            if (is_object($row)) {
                $row = (array) $row;
            }
            fputcsv($file, $row);
        }

        fclose($file);

        return [
            'success' => true,
            'filepath' => $filepath,
            'url' => $upload_dir['baseurl'] . '/kpi-dashboard/exports/' . sanitize_file_name($filename),
            'filename' => $filename,
            'size' => filesize($filepath),
        ];
    }

    /**
     * Export data to JSON
     */
    public static function to_json($data, $filename = 'export.json') {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/kpi-dashboard/exports';

        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }

        $filepath = $export_dir . '/' . sanitize_file_name($filename);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        file_put_contents($filepath, $json);

        return [
            'success' => true,
            'filepath' => $filepath,
            'url' => $upload_dir['baseurl'] . '/kpi-dashboard/exports/' . sanitize_file_name($filename),
            'filename' => $filename,
            'size' => filesize($filepath),
        ];
    }

    /**
     * Prepare KPI data for export
     */
    public static function prepare_kpi_data($kpi_data) {
        $export_data = [];

        foreach ($kpi_data as $entry) {
            $export_data[] = [
                'KPI ID' => $entry->kpi_id,
                'Department ID' => $entry->department_id,
                'Period Start' => $entry->period_start,
                'Period End' => $entry->period_end,
                'Value' => $entry->value,
                'Unit' => $entry->unit,
                'Status' => $entry->status,
                'Notes' => $entry->notes,
                'Submitted By' => $entry->submitted_by,
                'Submitted At' => $entry->submitted_at,
                'Reviewed By' => $entry->reviewed_by,
                'Reviewed At' => $entry->reviewed_at,
            ];
        }

        return $export_data;
    }

    /**
     * Cleanup old export files (older than 7 days)
     */
    public static function cleanup_old_exports() {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/kpi-dashboard/exports';

        if (!is_dir($export_dir)) {
            return;
        }

        $files = glob($export_dir . '/*');
        $cutoff = strtotime('-7 days');

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }

    /**
     * Generate export filename with timestamp
     */
    public static function generate_filename($prefix, $extension = 'csv') {
        return sprintf(
            '%s_%s.%s',
            sanitize_title($prefix),
            date('Y-m-d_His'),
            $extension
        );
    }
}

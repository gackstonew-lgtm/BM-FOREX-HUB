<?php
/**
 * BM Forex Hub — UTF-8 CSV Exporter Engine
 */

class CsvExporter {
    /**
     * Export user records to CSV format with UTF-8 BOM
     * @param array $users List of user arrays
     * @param array $selectedFields Fields to include
     * @return string CSV text content
     */
    public static function generate($users, $selectedFields = []) {
        if (empty($selectedFields)) {
            $selectedFields = ['full_name', 'username', 'email', 'phone', 'country', 'created_at', 'verification_status', 'account_status'];
        }

        $fieldLabels = [
            'full_name'           => 'Full Name',
            'username'            => 'Username',
            'email'               => 'Email Address',
            'phone'               => 'Phone Number',
            'country'             => 'Country',
            'created_at'          => 'Registration Date',
            'verification_status' => 'Verification Status',
            'account_status'      => 'Account Status'
        ];

        $output = fopen('php://temp', 'r+');

        // Write UTF-8 Byte Order Mark (BOM) for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Write Header Row
        $header = [];
        foreach ($selectedFields as $f) {
            $header[] = $fieldLabels[$f] ?? $f;
        }
        fputcsv($output, $header);

        // Write Data Rows
        foreach ($users as $u) {
            $row = [];
            foreach ($selectedFields as $f) {
                switch ($f) {
                    case 'full_name':
                        $name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                        $row[] = $name ?: ($u['username'] ?? 'N/A');
                        break;
                    case 'username':
                        $row[] = $u['username'] ?? '';
                        break;
                    case 'email':
                        $row[] = $u['email'] ?? '';
                        break;
                    case 'phone':
                        // Prefix phone with tab or apostrophe in CSV to preserve leading plus/zeroes
                        $row[] = $u['phone'] ? ("'" . $u['phone']) : '';
                        break;
                    case 'country':
                        $row[] = $u['country_code'] ?? ($u['country'] ?? '');
                        break;
                    case 'created_at':
                        $row[] = !empty($u['created_at']) ? date('Y-m-d H:i:s', strtotime($u['created_at'])) : '';
                        break;
                    case 'verification_status':
                        $row[] = !empty($u['is_verified']) ? 'Verified' : 'Unverified';
                        break;
                    case 'account_status':
                        $row[] = !empty($u['banned']) ? 'Banned' : (!empty($u['is_active']) ? 'Active' : 'Inactive');
                        break;
                    default:
                        $row[] = $u[$f] ?? '';
                        break;
                }
            }
            fputcsv($output, $row);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }
}

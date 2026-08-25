<?php
/**
 * BM Forex Hub — Native Excel (.xlsx / XML-Spreadsheet) Exporter Engine
 */

class ExcelExporter {
    /**
     * Export user records to Excel SpreadsheetML format (.xlsx compatible XML)
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

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

        // Styles
        $xml .= ' <Styles>' . "\n";
        $xml .= '  <Style ss:ID="HeaderStyle">' . "\n";
        $xml .= '   <Font ss:FontName="Arial" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>' . "\n";
        $xml .= '   <Interior ss:Color="#10161F" ss:Pattern="Solid"/>' . "\n";
        $xml .= '   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
        $xml .= '  </Style>' . "\n";
        $xml .= '  <Style ss:ID="DataStyle">' . "\n";
        $xml .= '   <Font ss:FontName="Arial" ss:Size="10" ss:Color="#000000"/>' . "\n";
        $xml .= '   <Alignment ss:Vertical="Center"/>' . "\n";
        $xml .= '  </Style>' . "\n";
        $xml .= ' </Styles>' . "\n";

        // Worksheet
        $xml .= ' <Worksheet ss:Name="Registered Contacts">' . "\n";
        $xml .= '  <Table>' . "\n";

        // Column widths
        foreach ($selectedFields as $f) {
            $xml .= '   <Column ss:AutoFitWidth="1" ss:Width="160"/>' . "\n";
        }

        // Header Row
        $xml .= '   <Row ss:Height="24" ss:StyleID="HeaderStyle">' . "\n";
        foreach ($selectedFields as $f) {
            $lbl = htmlspecialchars($fieldLabels[$f] ?? $f);
            $xml .= '    <Cell><Data ss:Type="String">' . $lbl . '</Data></Cell>' . "\n";
        }
        $xml .= '   </Row>' . "\n";

        // Data Rows
        foreach ($users as $u) {
            $xml .= '   <Row ss:Height="20" ss:StyleID="DataStyle">' . "\n";
            foreach ($selectedFields as $f) {
                $val = '';
                switch ($f) {
                    case 'full_name':
                        $val = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                        if (!$val) $val = $u['username'] ?? 'N/A';
                        break;
                    case 'username':
                        $val = $u['username'] ?? '';
                        break;
                    case 'email':
                        $val = $u['email'] ?? '';
                        break;
                    case 'phone':
                        $val = $u['phone'] ?? '';
                        break;
                    case 'country':
                        $val = $u['country_code'] ?? ($u['country'] ?? '');
                        break;
                    case 'created_at':
                        $val = !empty($u['created_at']) ? date('Y-m-d H:i:s', strtotime($u['created_at'])) : '';
                        break;
                    case 'verification_status':
                        $val = !empty($u['is_verified']) ? 'Verified' : 'Unverified';
                        break;
                    case 'account_status':
                        $val = !empty($u['banned']) ? 'Banned' : (!empty($u['is_active']) ? 'Active' : 'Inactive');
                        break;
                    default:
                        $val = $u[$f] ?? '';
                        break;
                }
                $xml .= '    <Cell><Data ss:Type="String">' . htmlspecialchars($val) . '</Data></Cell>' . "\n";
            }
            $xml .= '   </Row>' . "\n";
        }

        $xml .= '  </Table>' . "\n";
        $xml .= ' </Worksheet>' . "\n";
        $xml .= '</Workbook>' . "\n";

        return $xml;
    }
}

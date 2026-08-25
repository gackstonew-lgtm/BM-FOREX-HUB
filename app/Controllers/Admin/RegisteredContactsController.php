<?php
/**
 * BM Forex Hub — Admin Registered Contacts Controller
 */

require_once __DIR__ . '/../../Services/ContactExportService.php';
require_once __DIR__ . '/../../Exports/CsvExporter.php';
require_once __DIR__ . '/../../Exports/ExcelExporter.php';
require_once __DIR__ . '/../../Models/ContactExport.php';

class RegisteredContactsController {

    public static function handleRequest() {
        if (empty($_SESSION['admin_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized admin session.']);
            exit;
        }

        $adminId = $_SESSION['admin_user'] ?? $_SESSION['admin_id'] ?? 'admin';
        $input = json_decode(file_get_contents('php://input'), true) ?: $_REQUEST;
        $action = $input['action'] ?? $_GET['action'] ?? 'list';

        $allUsers = ContactExportService::fetchAllUsersEnriched();

        // 1. List Action (Paginated + Filtered + Summary Stats)
        if ($action === 'list') {
            $filtered = ContactExportService::filterUsers($allUsers, $input);
            $stats    = ContactExportService::getSummaryStats($allUsers);

            $page     = max(1, (int)($input['page'] ?? 1));
            $pageSize = min(100, max(1, (int)($input['pageSize'] ?? 20)));
            $total    = count($filtered);
            $offset   = ($page - 1) * $pageSize;

            $pagedUsers = array_slice($filtered, $offset, $pageSize);

            header('Content-Type: application/json');
            echo json_encode([
                'success'    => true,
                'users'      => $pagedUsers,
                'total'      => $total,
                'page'       => $page,
                'pageSize'   => $pageSize,
                'totalPages' => max(1, ceil($total / $pageSize)),
                'stats'      => $stats
            ]);
            exit;
        }

        // 2. Export Action (CSV or Excel download with Audit Logging)
        if ($action === 'export') {
            $format = strtolower($input['format'] ?? $_GET['format'] ?? 'csv');
            $selectedFields = is_array($input['fields'] ?? null) ? $input['fields'] : (is_string($input['fields'] ?? null) ? explode(',', $input['fields']) : []);
            
            $filtered = ContactExportService::filterUsers($allUsers, $input);
            $count = count($filtered);

            // Audit log recording
            ContactExport::logExport($adminId, $format, $count, $selectedFields, [
                'search'       => $input['search'] ?? '',
                'verification' => $input['verification'] ?? 'all',
                'status'       => $input['status'] ?? 'all',
                'country'      => $input['country'] ?? 'all'
            ]);

            $filename = 'BM_Forex_Contacts_' . date('Ymd_His');

            if ($format === 'xlsx' || $format === 'excel') {
                $content = ExcelExporter::generate($filtered, $selectedFields);
                header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
                header('Cache-Control: max-age=0');
                echo $content;
            } else {
                $content = CsvExporter::generate($filtered, $selectedFields);
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
                header('Cache-Control: max-age=0');
                echo $content;
            }
            exit;
        }

        // 3. Audit Logs History Action
        if ($action === 'audit_logs') {
            header('Content-Type: application/json');
            $logs = ContactExport::getAuditLogs(100);
            echo json_encode(['success' => true, 'audit_logs' => $logs]);
            exit;
        }

        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid contacts API action.']);
    }
}

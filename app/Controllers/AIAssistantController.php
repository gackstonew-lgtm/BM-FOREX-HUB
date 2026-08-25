<?php
/**
 * BM Forex Hub — AI Assistant Public Controller
 */

require_once __DIR__ . '/../Services/AIService.php';
require_once __DIR__ . '/../Models/AIConversation.php';

class AIAssistantController {

    public static function handleRequest() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['ai_session_id'])) {
            $_SESSION['ai_session_id'] = 'sess_' . bin2hex(random_bytes(8));
        }

        $sessionId = $_SESSION['ai_session_id'];
        $userId    = $_SESSION['admin_user'] ?? $_SESSION['user_id'] ?? $_SESSION['username'] ?? 'visitor';

        $input  = json_decode(file_get_contents('php://input'), true) ?: $_REQUEST;
        $action = $input['action'] ?? $_GET['action'] ?? 'chat';

        header('Content-Type: application/json');

        // Action: Init Config & State
        if ($action === 'init') {
            $settings = AIConversation::getAllSettings();
            echo json_encode([
                'success'             => true,
                'bot_name'            => 'BM Forex Hub AI',
                'welcome_message'     => $settings['welcome_message'],
                'suggested_questions' => $settings['suggested_questions'],
                'enabled'             => $settings['enabled'],
                'whatsapp_url'        => $settings['whatsapp_url'],
                'session_id'          => $sessionId
            ]);
            exit;
        }

        // Action: Process Chat Prompt
        if ($action === 'chat') {
            $message = trim($input['message'] ?? '');
            if (empty($message)) {
                echo json_encode(['success' => false, 'error' => 'Message prompt cannot be empty.']);
                exit;
            }

            // Rate Limit Check (max 30 msgs per minute per session)
            $now = time();
            $_SESSION['ai_msg_count'] = ($_SESSION['ai_msg_count'] ?? 0) + 1;
            $_SESSION['ai_last_time'] = $_SESSION['ai_last_time'] ?? $now;
            
            if ($now - $_SESSION['ai_last_time'] > 60) {
                $_SESSION['ai_msg_count'] = 1;
                $_SESSION['ai_last_time'] = $now;
            } elseif ($_SESSION['ai_msg_count'] > 30) {
                echo json_encode([
                    'success' => false,
                    'reply'   => "You are sending messages too quickly. Please wait a moment before asking another question.",
                    'error'   => 'Rate limit exceeded'
                ]);
                exit;
            }

            $response = AIService::processPrompt($message, $sessionId, $userId);

            echo json_encode([
                'success'      => true,
                'reply'        => $response['reply'],
                'escalate'     => $response['escalate'] ?? false,
                'whatsapp_url' => $response['whatsapp_url'] ?? $settings['whatsapp_url'] ?? 'https://wa.me/message/K5RM7MSWXBNPC1',
                'action_route' => $response['action_route'] ?? null,
                'off_topic'    => $response['off_topic'] ?? false
            ]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid AI Assistant action.']);
    }
}

<?php
/**
 * BM Forex Hub — Contact Directory & Export Service Layer
 */

require_once __DIR__ . '/../../admin/config.php';

class ContactExportService {

    /**
     * Fetch all users from Supabase API (chunked)
     */
    public static function fetchAllUsersEnriched() {
        $allProfiles = [];
        $page = 1;
        $pageSize = 500;

        while (true) {
            $offset = ($page - 1) * $pageSize;
            $res = sb_admin_get('profiles', [
                'select' => 'id,username,first_name,last_name,created_at,country_code,phone,role',
                'limit'  => $pageSize,
                'offset' => $offset
            ]);
            $list = $res['data'] ?? [];
            if (!is_array($list) || empty($list)) break;
            $allProfiles = array_merge($allProfiles, $list);
            if (count($list) < $pageSize) break;
            $page++;
        }

        // Fetch auth users for email, verification & sign-in status
        $authUsers = sb_auth_admin_users('?per_page=1000');
        $authList = [];
        $authData = $authUsers['data'] ?? null;
        if (is_array($authData)) {
            if (isset($authData['users']) && is_array($authData['users'])) {
                $authList = $authData['users'];
            } elseif (isset($authData[0]) && is_array($authData[0]['users'] ?? null)) {
                $authList = $authData[0]['users'];
            }
        }

        $authMap = [];
        foreach ($authList as $au) {
            $uid = $au['id'] ?? '';
            if ($uid) {
                $authMap[$uid] = [
                    'email'           => $au['email'] ?? '',
                    'email_confirmed' => (!empty($au['email_confirmed_at']) || !empty($au['confirmed_at'])),
                    'last_sign_in'    => $au['last_sign_in_at'] ?? null,
                    'banned'          => $au['banned_until'] ?? null,
                ];
            }
        }

        $now = time();
        $oneWeekAgo = $now - (7 * 86400);

        $enriched = [];
        foreach ($allProfiles as $p) {
            $uid   = $p['id'] ?? '';
            $auth  = $authMap[$uid] ?? [];
            $email = strtolower(trim($auth['email'] ?? ''));

            $isVerified   = !empty($auth['email_confirmed']);
            $lastSignIn   = !empty($auth['last_sign_in']) ? strtotime($auth['last_sign_in']) : 0;
            $isActive     = ($lastSignIn > $oneWeekAgo);

            $firstName = $p['first_name'] ?? '';
            $lastName  = $p['last_name'] ?? '';
            $fullName  = trim("$firstName $lastName");

            $enriched[] = [
                'id'                  => $uid,
                'username'            => $p['username'] ?? '',
                'first_name'          => $firstName,
                'last_name'           => $lastName,
                'full_name'           => $fullName ?: ($p['username'] ?? 'N/A'),
                'email'               => $email,
                'phone'               => $p['phone'] ?? '',
                'country_code'        => $p['country_code'] ?? '',
                'role'                => $p['role'] ?? 'user',
                'created_at'          => $p['created_at'] ?? '',
                'is_verified'         => $isVerified,
                'is_active'           => $isActive,
                'banned'              => $auth['banned'] ?? null,
                'last_sign_in'        => $auth['last_sign_in'] ?? null,
            ];
        }

        return $enriched;
    }

    /**
     * Filter user list based on search term & filter parameters
     */
    public static function filterUsers($allUsers, $filters = []) {
        $search = strtolower(trim($filters['search'] ?? ''));
        $verification = $filters['verification'] ?? 'all'; // verified, unverified
        $status       = $filters['status'] ?? 'all';       // active, inactive, banned
        $country      = strtoupper(trim($filters['country'] ?? 'all'));
        $dateFrom     = !empty($filters['date_from']) ? strtotime($filters['date_from']) : 0;
        $dateTo       = !empty($filters['date_to']) ? strtotime($filters['date_to'] . ' 23:59:59') : 0;
        $selectedIds  = is_array($filters['selected_ids'] ?? null) ? $filters['selected_ids'] : [];

        $filtered = [];
        foreach ($allUsers as $u) {
            // Selected IDs filter if specified
            if (!empty($selectedIds) && !in_array($u['id'], $selectedIds) && !in_array($u['email'], $selectedIds)) {
                continue;
            }

            // Search query across Full Name, Username, Email, Phone
            if ($search !== '') {
                $haystack = strtolower(($u['full_name'] ?? '') . ' ' . ($u['username'] ?? '') . ' ' . ($u['email'] ?? '') . ' ' . ($u['phone'] ?? ''));
                if (strpos($haystack, $search) === false) {
                    continue;
                }
            }

            // Verification Filter
            if ($verification === 'verified' && !$u['is_verified']) continue;
            if ($verification === 'unverified' && $u['is_verified']) continue;

            // Account Status Filter
            if ($status === 'active' && !$u['is_active']) continue;
            if ($status === 'inactive' && $u['is_active']) continue;
            if ($status === 'banned' && empty($u['banned'])) continue;

            // Country Filter
            if ($country !== 'ALL' && strtoupper($u['country_code']) !== $country) continue;

            // Date Range Filter
            if (!empty($u['created_at'])) {
                $joinedTime = strtotime($u['created_at']);
                if ($dateFrom > 0 && $joinedTime < $dateFrom) continue;
                if ($dateTo > 0 && $joinedTime > $dateTo) continue;
            }

            $filtered[] = $u;
        }

        return $filtered;
    }

    /**
     * Compute Summary Metric Statistics
     */
    public static function getSummaryStats($allUsers) {
        $total    = count($allUsers);
        $verified = 0;
        $hasPhone = 0;
        $hasEmail = 0;
        $todayReg = 0;

        $todayStr = date('Y-m-d');

        foreach ($allUsers as $u) {
            if ($u['is_verified']) $verified++;
            if (!empty($u['phone'])) $hasPhone++;
            if (!empty($u['email'])) $hasEmail++;

            if (!empty($u['created_at']) && strpos($u['created_at'], $todayStr) === 0) {
                $todayReg++;
            }
        }

        return [
            'total_users'      => $total,
            'verified_users'   => $verified,
            'users_with_phone' => $hasPhone,
            'users_with_email' => $hasEmail,
            'today_registrations' => $todayReg
        ];
    }
}

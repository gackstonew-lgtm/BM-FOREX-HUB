<?php
namespace App\Services;

/**
 * LiveClassStatusEngine
 * Calculates dynamic statuses, meeting readiness, countdowns, and automated status transitions.
 */
class LiveClassStatusEngine {
    
    /**
     * Process a raw class record and enrich it with computed statuses and formatting
     */
    public static function processClass(array $classItem): array {
        $now = time();
        
        // Resolve start_datetime
        $startStr = $classItem['start_datetime'] ?? '';
        if (empty($startStr) && !empty($classItem['class_date']) && !empty($classItem['class_time'])) {
            $startStr = $classItem['class_date'] . ' ' . $classItem['class_time'];
        }
        $startTs = !empty($startStr) ? strtotime($startStr) : $now;
        
        $durationMinutes = (int)($classItem['duration_minutes'] ?? $classItem['duration'] ?? 60);
        if ($durationMinutes <= 0) $durationMinutes = 60;
        
        $endTs = !empty($classItem['end_datetime']) ? strtotime($classItem['end_datetime']) : ($startTs + ($durationMinutes * 60));
        
        // Manual override flags
        $isLiveOverride = !empty($classItem['is_live']);
        
        // 1. Calculate live_status
        $liveStatus = $classItem['live_status'] ?? '';
        if ($isLiveOverride) {
            $liveStatus = 'live';
        } elseif (empty($liveStatus) || in_array($liveStatus, ['scheduled', 'upcoming', 'live', 'ended'])) {
            if ($now < $startTs) {
                $liveStatus = ($startTs - $now <= 86400) ? 'scheduled' : 'upcoming';
            } elseif ($now >= $startTs && $now <= $endTs) {
                $liveStatus = 'live';
            } else {
                $liveStatus = 'ended';
            }
        }
        
        // 2. Calculate meeting_status
        $meetingStatus = $classItem['meeting_status'] ?? '';
        if ($isLiveOverride) {
            $meetingStatus = 'open';
        } elseif (empty($meetingStatus) || in_array($meetingStatus, ['waiting', 'open', 'in_progress', 'completed', 'expired'])) {
            if ($now < $startTs) {
                $meetingStatus = 'waiting';
            } elseif ($now >= $startTs && $now <= $endTs) {
                $meetingStatus = 'open';
            } else {
                $meetingStatus = 'completed';
            }
        }
        
        $isLive = ($liveStatus === 'live' || $isLiveOverride);
        $meetingReady = (!empty($classItem['meeting_ready']) || $meetingStatus === 'open' || $isLive);
        $countdownSeconds = max(0, $startTs - $now);
        
        // Formatting helpers
        $classDate = date('Y-m-d', $startTs);
        $classTime = date('h:i A', $startTs);
        $formattedDate = date('F j, Y', $startTs);
        $timezone = $classItem['timezone'] ?? 'Africa/Nairobi (EAT)';
        
        return array_merge($classItem, [
            'title'             => $classItem['title'] ?? 'Untitled Live Class',
            'description'       => $classItem['description'] ?? '',
            'instructor_name'   => $classItem['instructor_name'] ?? $classItem['instructor'] ?? 'BM Forex Hub Team',
            'instructor'        => $classItem['instructor_name'] ?? $classItem['instructor'] ?? 'BM Forex Hub Team',
            'instructor_photo'  => $classItem['instructor_photo'] ?? '',
            'meeting_platform'  => $classItem['meeting_platform'] ?? 'Google Meet',
            'meeting_link'      => $classItem['meeting_link'] ?? '',
            'meeting_id'        => $classItem['meeting_id'] ?? '',
            'meeting_password'  => $classItem['meeting_password'] ?? '',
            'start_datetime'    => date('c', $startTs),
            'end_datetime'      => date('c', $endTs),
            'timezone'          => $timezone,
            'duration_minutes'  => $durationMinutes,
            'class_date'        => $classDate,
            'class_time'        => $classTime,
            'formatted_date'    => $formattedDate,
            'class_banner'      => $classItem['class_banner'] ?? '',
            'class_notes_pdf'   => $classItem['class_notes_pdf'] ?? '',
            'recording_link'    => $classItem['recording_link'] ?? '',
            'max_attendees'     => (int)($classItem['max_attendees'] ?? 500),
            'visibility'        => $classItem['visibility'] ?? 'published',
            'live_status'       => $liveStatus,
            'meeting_status'    => $meetingStatus,
            'is_live'           => $isLive,
            'meeting_ready'     => $meetingReady,
            'countdown_seconds' => $countdownSeconds,
        ]);
    }
}

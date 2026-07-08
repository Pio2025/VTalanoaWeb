<?php

namespace App\Services;

use App\Models\ParticipantModel;

class AnalyticsService
{
    private ParticipantModel $participantModel;

    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
    }

    public function getStats(array $meeting): array
    {
        $participants = $this->participantModel->getForStats((int) $meeting['meeting_id']);

        $identities = [];
        $attendanceDurations = [];

        foreach ($participants as $p) {
            $identity = $p['user_id'] ?? ('guest:' . ($p['guest_email'] ?: $p['guest_name'] ?: $p['participant_id']));
            $identities[$identity] = true;

            if (!empty($p['joined_at']) && !empty($p['left_at'])) {
                $attendanceDurations[] = strtotime($p['left_at']) - strtotime($p['joined_at']);
            }
        }

        $durationSeconds = null;
        if (!empty($meeting['actual_start']) && !empty($meeting['actual_end'])) {
            $durationSeconds = strtotime($meeting['actual_end']) - strtotime($meeting['actual_start']);
        }

        $avgAttendanceSeconds = $attendanceDurations
            ? (int) round(array_sum($attendanceDurations) / count($attendanceDurations))
            : null;

        return [
            'total_participants'     => count($identities),
            'duration_seconds'       => $durationSeconds,
            'avg_attendance_seconds' => $avgAttendanceSeconds,
            'timeline'               => $this->buildTimeline($meeting, $participants),
        ];
    }

    private function buildTimeline(array $meeting, array $participants): array
    {
        if (empty($meeting['actual_start'])) {
            return [];
        }

        $start = strtotime($meeting['actual_start']);
        $end   = !empty($meeting['actual_end']) ? strtotime($meeting['actual_end']) : time();

        if ($end <= $start) {
            return [];
        }

        $attendees = array_filter($participants, static fn ($p) => !empty($p['joined_at']));
        if (!$attendees) {
            return [];
        }

        $buckets    = 12;
        $stepSeconds = max(1, (int) round(($end - $start) / $buckets));
        $timeline   = [];

        for ($i = 0; $i <= $buckets; $i++) {
            $t = min($end, $start + ($i * $stepSeconds));

            $count = 0;
            foreach ($attendees as $p) {
                $joinedAt = strtotime($p['joined_at']);
                $leftAt   = !empty($p['left_at']) ? strtotime($p['left_at']) : null;
                if ($joinedAt <= $t && ($leftAt === null || $leftAt >= $t)) {
                    $count++;
                }
            }

            $timeline[] = ['t' => date('H:i', $t), 'count' => $count];
        }

        return $timeline;
    }
}

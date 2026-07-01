<?php
namespace App\Models;
use CodeIgniter\Model;

class WsCalendarModel extends Model
{
    protected $table      = 'workspace_calendar_events';
    protected $primaryKey = 'event_id';
    protected $returnType = 'array';
    protected $allowedFields = ['user_id', 'title', 'description', 'start_time', 'end_time', 'is_all_day', 'meeting_id', 'color'];
    protected $useTimestamps = true;

    public function getForUser(int $userId, string $startDate, string $endDate): array
    {
        // Own events
        $own = $this->where('user_id', $userId)
            ->where('start_time >=', $startDate)
            ->where('start_time <=', $endDate)
            ->orderBy('start_time', 'ASC')
            ->findAll();

        // Meetings from the meetings table (scheduled)
        $db = \Config\Database::connect();
        $meetings = $db->select('meeting_id AS meeting_id_src, title, scheduled_start AS start_time,
            IFNULL(scheduled_end, DATE_ADD(scheduled_start, INTERVAL 60 MINUTE)) AS end_time,
            "#3b82f6" AS color, 1 AS is_meeting')
            ->from('meetings')
            ->where('host_user_id', $userId)
            ->where('status', 'Scheduled')
            ->where('scheduled_start >=', $startDate)
            ->where('scheduled_start <=', $endDate)
            ->get()->getResultArray();

        return array_merge($own, $meetings);
    }
}

<?php
namespace App\Controllers\Workspace;

use App\Controllers\BaseController;

class CalendarController extends BaseController
{
    /** GET /api/workspace/calendar?start=YYYY-MM-DD&end=YYYY-MM-DD
     *  Works without the workspace_calendar_events table — meetings are always shown.
     *  Custom events are shown only when the table exists.
     */
    public function events(): \CodeIgniter\HTTP\ResponseInterface
    {
        $user  = session('auth_user');
        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end')   ?? date('Y-m-t 23:59:59');
        $db    = \Config\Database::connect();

        // Always-available: scheduled meetings the user hosts
        $meetings = $db->table('meetings')
            ->select('meeting_id, title,
                scheduled_start AS start_time,
                IFNULL(scheduled_end, DATE_ADD(scheduled_start, INTERVAL 60 MINUTE)) AS end_time,
                "#3b82f6" AS color, 1 AS is_meeting, NULL AS event_id')
            ->where('host_user_id', $user['user_id'])
            ->where('status', 'Scheduled')
            ->where('scheduled_start >=', $start)
            ->where('scheduled_start <=', $end)
            ->get()->getResultArray();

        // Optional: custom events (only if the table exists)
        $own = [];
        try {
            $own = $db->table('workspace_calendar_events')
                ->where('user_id', $user['user_id'])
                ->where('start_time >=', $start)
                ->where('start_time <=', $end)
                ->orderBy('start_time', 'ASC')
                ->get()->getResultArray();
        } catch (\Exception $e) {
            // Table not yet created — custom events silently skipped
        }

        usort($all = array_merge($own, $meetings), fn($a, $b) => strcmp($a['start_time'], $b['start_time']));
        return $this->response->setJSON($all);
    }

    /** POST /api/workspace/calendar — requires workspace_calendar_events table */
    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = session('auth_user');
        $data = $this->request->getJSON(true) ?? [];
        if (empty($data['title']) || empty($data['start_time'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'title and start_time required']);
        }
        try {
            $db = \Config\Database::connect();
            $db->table('workspace_calendar_events')->insert([
                'user_id'     => $user['user_id'],
                'title'       => trim($data['title']),
                'description' => trim($data['description'] ?? ''),
                'start_time'  => $data['start_time'],
                'end_time'    => $data['end_time'] ?? $data['start_time'],
                'is_all_day'  => (int)($data['is_all_day'] ?? 0),
                'color'       => $data['color'] ?? '#00aeef',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            $id  = $db->insertID();
            $row = $db->table('workspace_calendar_events')->where('event_id', $id)->get()->getRowArray();
            return $this->response->setJSON($row);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(503)->setJSON(['error' => 'Custom events table not set up yet']);
        }
    }

    /** PUT /api/workspace/calendar/{id} */
    public function update(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = session('auth_user');
        try {
            $db    = \Config\Database::connect();
            $event = $db->table('workspace_calendar_events')->where('event_id', $id)->get()->getRowArray();
            if (!$event || (int)$event['user_id'] !== (int)$user['user_id']) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
            }
            $data = $this->request->getJSON(true) ?? [];
            $upd  = array_intersect_key($data, array_flip(['title','description','start_time','end_time','is_all_day','color']));
            if ($upd) {
                $upd['updated_at'] = date('Y-m-d H:i:s');
                $db->table('workspace_calendar_events')->where('event_id', $id)->update($upd);
            }
            return $this->response->setJSON($db->table('workspace_calendar_events')->where('event_id', $id)->get()->getRowArray());
        } catch (\Exception $e) {
            return $this->response->setStatusCode(503)->setJSON(['error' => 'Custom events table not set up yet']);
        }
    }

    /** DELETE /api/workspace/calendar/{id} */
    public function destroy(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $user = session('auth_user');
        try {
            $db    = \Config\Database::connect();
            $event = $db->table('workspace_calendar_events')->where('event_id', $id)->get()->getRowArray();
            if (!$event || (int)$event['user_id'] !== (int)$user['user_id']) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
            }
            $db->table('workspace_calendar_events')->where('event_id', $id)->delete();
            return $this->response->setJSON(['ok' => true]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(503)->setJSON(['error' => 'Custom events table not set up yet']);
        }
    }
}

<?php

namespace App\Controllers;

use App\Models\MeetingModel;
use App\Models\ParticipantModel;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $user         = session()->get('auth_user');
        $meetingModel = new MeetingModel();

        $upcoming = $meetingModel->getUpcoming($user['user_id'], 5);
        $recent   = $meetingModel->where('host_user_id', $user['user_id'])
                                  ->whereIn('status', ['Ended', 'Cancelled'])
                                  ->orderBy('actual_end', 'DESC')
                                  ->limit(5)
                                  ->findAll();
        $total    = $meetingModel->where('host_user_id', $user['user_id'])->countAllResults();

        return view('dashboard/index', [
            'title'    => 'Dashboard — VTalanoa',
            'user'     => $user,
            'upcoming' => $upcoming,
            'recent'   => $recent,
            'total'    => $total,
        ]);
    }
}

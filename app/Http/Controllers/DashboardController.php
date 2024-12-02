<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $open_task_statuses = [2]; //ONGOING

        $not_started_tasks = Task::all()
            ->where('manager', auth()->user()->id)
            ->where('status', '=', 1)
            ->count();

        $open_tasks = Task::all()
            ->where('manager', auth()->user()->id)
            ->whereIn('status', $open_task_statuses)
            ->count();

        $on_hold_tasks = Task::all()
            ->where('manager', auth()->user()->id)
            ->where('status', '=', 3)
            ->count();

        return Inertia::render('Dashboard', [
            'open_tasks' => $open_tasks,
            'not_started_tasks' => $not_started_tasks,
            'on_hold_tasks' => $on_hold_tasks,
        ]);
    }
}

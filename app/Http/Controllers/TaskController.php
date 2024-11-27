<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Contractor;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::orderByDesc('created_at')
            ->with('subtasks', 'comments', 'subtasks.comments', 'contractor')
            ->get();
        $contractors = Contractor::all();
        $statuses = Status::all();
        $users = User::where('role_id', '!=', 2)->get();
        $currentUserRole = auth()->user()->role->name;

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'statuses' => $statuses,
            'contractors' => $contractors,
            'users' => $users,
            'currentUserRole' => $currentUserRole
        ]);
    }

    public function getComments(Task $task)
    {
       return Comment::where('task_id', $task->id)->orderByDesc('created_at')->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $newTask = Task::create(
            $request->validate([
                'title' => ['required'],
                'description' => ['required'],
                'manager' => ['nullable', 'integer'],
                'contractor' => ['required', 'integer'],
                'cost' => ['numeric'],
                'currency' => ['required'],
                'parent_task' => ['nullable', 'integer'],
                'priority' => ['string', 'required'],
            ])
        );
        $newTask->update([
            'created_by' => auth()->user()->id,
            'deadline_start' => Carbon::now(),
            'deadline_end' => Carbon::now()->addDays(14),
        ]);

        if ($request['parent_task'] != null) {
          $newTask->update([
             'is_subtask' => true,
          ]);
        };

        if ($request['manager'] != null && auth()->user()->role->name === 'head-of-department') {
            $newTask->update([
                'manager' => $request['manager'],
            ]);
        } elseif ($request['manager'] === null && auth()->user()->role->name === 'user') {
            $newTask->update([
                'manager' => $newTask->created_by,
            ]);
        }

        return redirect()->route('tasks.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $task->update(
            $request->validate([
                'title' => ['required'],
                'description' => ['required'],
                'contractor' => ['required', 'integer'],
                'cost' => ['numeric'],
                'currency' => ['required'],
                'parent_task' => ['nullable', 'integer'],
                'priority' => ['string', 'required'],
            ])
        );

        if ($request['contractor'] != null) {
            $task->subtasks()->update([
                'contractor' => $request['contractor'],
            ]);
        }

        if ($request['manager'] != null && auth()->user()->role->name === 'head-of-department') {
            $task->update([
                'manager' => $request['manager'],
            ]);
        }

        return redirect()->route('tasks.index');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $task->update(
          $request->validate([
            'status' => ['required', 'exists:statuses,id'],
          ])
        );

        return redirect()->route('tasks.index');
    }

    public function updateDeadline(Request $request, Task $task)
    {
        $task->update(
            $request->validate([
                'deadline_end' => ['required', 'date'],
            ])
        );

        return redirect()->route('tasks.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }
}

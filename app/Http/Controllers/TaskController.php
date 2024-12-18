<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Contractor;
use App\Models\Department;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentUser = auth()->user();
        $currentUserRole = auth()->user()->role->name;

        $users = User::where('role_id', '!=', 1)->where('name', '!=', 'Антон Андреев')->get();
        $statuses = Status::all();
//        $projects = Project::with('tasks');

        $tasks = Task::orderByDesc('created_at')->with('subtasks', 'comments', 'subtasks.comments', 'contractor')->get();
        $contractors = Contractor::orderBy('name')->get();

        $dpToolsId = Department::where('name', '=', 'Инструменты')->first()->id;
        $dpEqpId = Department::where('name', '=', 'Оборудование')->first()->id;

        if ($currentUser->department->name == 'Оборудование') {
            $eqpStatuses = [2, 3, 5, 6];
            $statuses = Status::whereIn('id', $eqpStatuses)->get();
            $users = User::where('role_id', '!=', 1)->where('name', '!=', 'Антон Андреев')
                ->where('department_id', '=', $dpEqpId)
                ->get();
            $tasks = Task::orderByDesc('created_at')
                ->where('department_id', '=', $dpEqpId)
                ->with('subtasks', 'comments', 'subtasks.comments', 'contractor', 'project')
                ->get();
            $projects = Project::with(['tasks', 'tasks.comments', 'tasks.subtasks', 'tasks.contractor'])
                ->where('department_id', '=', $dpEqpId)
                ->get();
        } elseif ($currentUser->department->name == 'Инструменты') {
            $statuses = Status::all();
            $users = User::where('role_id', '!=', 1)->where('name', '!=', 'Антон Андреев')->where('department_id', '=', Department::where('name', '=', 'Инструменты')->first()->id)->get();
            $tasks = Task::orderByDesc('created_at')
                ->where('department_id', '=', Department::where('name', '=', 'Инструменты')->first()->id)
                ->with('subtasks', 'comments', 'subtasks.comments', 'contractor', 'project')
                ->get();
            $projects = [];
        }

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'statuses' => $statuses,
            'contractors' => $contractors,
            'users' => $users,
            'currentUserRole' => $currentUserRole,
            'currentUserDepartment' => $currentUser->department,
            'projects' => $projects,
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
        $newTask = Task::create($request->validate([
            'title' => ['required'],
            'description' => ['required'],
            'manager' => ['nullable', 'integer'],
            'contractor' => ['required', 'integer'],
            'cost' => ['nullable'],
            'currency' => ['required'],
            'parent_task' => ['nullable', 'integer'],
            'priority' => ['string', 'required'],
            'project_id' => ['nullable', 'integer'],
        ]));

        $newTask->update([
            'progress' => 0,
            'department_id' => auth()->user()->department_id,
        ]);

        if ($request['project_id'] === null) {
            $newTask->update([
                'project_id' => 1,
            ]);
        }

        if ($request['deadline_end'] !== null) {
            $deadlineEnd = Carbon::parse($request['deadline_end']);
            $newTask->update(['deadline_end' => $deadlineEnd]);
        } elseif (!($request['deadline_end']) || $request['deadline_end'] === null) {
            $newTask->update(['deadline_end' => Carbon::now()->addDays(14)]);
        }

        if ($request['cost'] === null) {
            $newTask->update(['cost' => 0]);
        }

        $newTask->update(['created_by' => auth()->user()->id, 'deadline_start' => Carbon::now()]);

        if ($request['parent_task'] != null) {
            $newTask->update(['is_subtask' => true, 'contractor' => Task::find($request['parent_task'])->contractor]);
        }

        if ($request['manager'] != null && auth()->user()->role->name === 'head-of-department') {
            $newTask->update(['manager' => $request['manager']]);
        } elseif ($request['manager'] === null && auth()->user()->role->name === 'user') {
            $newTask->update(['manager' => $newTask->created_by]);
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
        $task->update($request->validate(['title' => ['required'], 'description' => ['required'], 'contractor' => ['required', 'integer'], 'cost' => ['numeric'], 'currency' => ['required'], 'parent_task' => ['nullable', 'integer'], 'priority' => ['string', 'required']]));

        if ($request['contractor'] != null) {
            $task->subtasks()->update(['contractor' => $request['contractor']]);
        }

        if ($request['manager'] != null && auth()->user()->role->name === 'head-of-department') {
            $task->update(['manager' => $request['manager']]);
        }

        return redirect()->route('tasks.index');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $task->update($request->validate(['status' => ['required', 'exists:statuses,id']]));

        return redirect()->route('tasks.index');
    }

    public function updateDeadline(Request $request, Task $task)
    {
        $task->update($request->validate(['deadline_end' => ['required', 'date']]));

        return redirect()->route('tasks.index');
    }

    public function updateProgress(Request $request, Task $task)
    {
        $task->update($request->validate(['progress' => ['required', 'integer']]));

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

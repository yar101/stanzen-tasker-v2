<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\Status;
use App\Models\Task;
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
        $tasks = Task::orderByDesc('created_at')->get();
        $contractors = Contractor::all();
        $statuses = Status::all();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'statuses' => $statuses,
            'contractors' => $contractors,
        ]);
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
                'contractor' => ['required', 'integer'],
                'cost' => ['numeric'],
                'currency' => ['required'],
                'parent_task' => ['nullable', 'integer'],
            ])
        );
        $newTask->update([
            'created_by' => auth()->user()->id,
            'deadline_start' => Carbon::now(),
            'deadline_end' => Carbon::now()->addDays(14),
        ]);

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

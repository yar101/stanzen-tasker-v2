<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function Termwind\render;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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


        $newProject = Project::create(
            $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('projects', 'name')],
                'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            ])
        );

        if ($request['deadline_start'] !== null) {
            $deadlineStart = Carbon::parse($request['deadline_start']);
            $newProject->update(['deadline_start' => $deadlineStart]);
        };

        if ($request['deadline_end'] !== null) {
            $deadlineEnd = Carbon::parse($request['deadline_end']);
            $newProject->update(['deadline_end' => $deadlineEnd]);
        };

        return redirect()->route('tasks.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
    }
}

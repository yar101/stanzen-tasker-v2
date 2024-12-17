<?php

namespace App\Imports;

use App\Models\Comment;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class TasksImport implements ToCollection, WithChunkReading
{
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {

            Contractor::firstOrCreate([
                'name' => $row[4],
            ]);

            $task = Task::create([
                'title' => $row[0] ? $row[0] : 'Без темы',
                'description' => $row[1] ? $row[1] : 'Без описания',
                'manager' => $row[2],
                'status' => $row[3],
                'contractor' => $row[4] ? Contractor::firstWhere('name', $row[4])->id : 1,
                'cost' => $row[5] ? $row[5] : 0,
                'currency' => $row[6] ? $row[6] : 'RUB',
                'priority' => $row[7],
                'deadline_start' => Carbon::now(),
                'department_id' => User::find($row[2])->department->id,
                'project_id' => Project::where('name', '=', 'Без проекта')->first()->id,
            ]);

            if ($row[8]) {
                Comment::create([
                    'content' => $row[8],
                    'created_by' => $row[2],
                    'task_id' => $task->id,
                ]);
            };

        }
    }

    public function chunkSize(): int
    {
        return 50;
    }
}

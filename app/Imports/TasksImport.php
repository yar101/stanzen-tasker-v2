<?php

namespace App\Imports;

use App\Models\Comment;
use App\Models\Contractor;
use App\Models\Task;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class TasksImport implements ToCollection
{
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {

            Contractor::firstOrCreate([
                'name' => $row[4],
            ]);

            $task = Task::create([
                'title' => $row[0],
                'description' => $row[1],
                'manager' => $row[2],
                'status' => $row[3],
                'contractor' => Contractor::firstWhere('name', $row[4])->id,
                'cost' => $row[5],
                'currency' => $row[6] ? $row[6] : 'RUB',
                'priority' => $row[7],
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
}

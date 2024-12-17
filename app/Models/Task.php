<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\HigherOrderCollectionProxy;

class Task extends Model
{
    use HasFactory;

    /**
     * @var HigherOrderCollectionProxy|mixed
     */
    protected $guarded = [];


    public function project() :BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function status() :BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function contractor() :BelongsTo
    {
        return $this->belongsTo(Contractor::class, 'contractor');
    }

    public function subtasks() :HasMany
    {
        return $this->hasMany(Task::class, 'parent_task');
    }

    public function comments() :HasMany
    {
        $comments = $this->hasMany(Comment::class, 'task_id', 'id');
        return $comments->orderByDesc('created_at');
    }

    public function isSubtask() :bool
    {
            return $this->parent_task !== null;
    }

    public function isParent() :bool
    {
        return $this->parent_task === null;
    }

    public function getParentId()
    {
        return Task::find($this->parent_task)->id;
    }
}

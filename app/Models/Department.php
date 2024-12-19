<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Department extends Model
{
    protected $guarded = [];

    public function contractors() :BelongsToMany
    {
        return $this->belongsToMany(Contractor::class);
    }
}

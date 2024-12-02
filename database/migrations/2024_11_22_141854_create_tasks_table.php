<?php

use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use App\Models\Status;
use App\Models\Contractor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained();
            $table->foreignIdFor(User::class, 'manager')->nullable()->constrained();
            $table->foreignIdFor(Status::class, 'status')->default(2)->constrained();
            $table->foreignIdFor(Contractor::class, 'contractor')->default(1)->constrained();
            $table->foreignIdFor(Department::class, 'department_id')->default(1)->constrained();
            $table->date('deadline_start')->nullable();
            $table->date('deadline_end')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->foreignIdFor(Task::class, 'parent_task')->nullable()->constrained();
            $table->boolean('is_subtask')->default(false);
            $table->string('currency');
            $table->string('priority')->default('III');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

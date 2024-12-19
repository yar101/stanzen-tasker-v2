<?php

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
        Schema::create('contractor_department', function (Blueprint $table) {
            $table->unsignedBigInteger('contractor_id');
            $table->unsignedBigInteger('department_id');

            $table->foreign('contractor_id')
                ->references('id')
                ->on('contractors')->onDelete('cascade');

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractor_department');
    }
};

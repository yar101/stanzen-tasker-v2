<?php

namespace Database\Seeders;

use App\Models\Contractor;
use App\Models\Department;
use App\Models\Role;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Contractors
        Contractor::create(['name' => 'Без контрагента']);

        // FAKE! Contractors
        Contractor::factory(100)->create();

        // Create Departments
        Department::create(['name' => 'Без отдела']);
        Department::create(['name' => 'Инструменты']);

        // Create Roles
        Role::create(['name' => 'user']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'head-of-department']);

        // Create Admin
        User::factory()->create(['name' => 'admin', 'email' => 'yakimec@stnzn.ru', 'password' => Hash::make('Redandwhite1'), 'role_id' => Role::where('name', 'admin')->first()->id]);

        // Create Statuses
        Status::create(['name' => 'NOT STARTED']);
        Status::create(['name' => 'ONGOING']);
        Status::create(['name' => 'ON HOLD']);
        Status::create(['name' => 'DELAY']);
        Status::create(['name' => 'DONE']);

        // Create test Tasks
        Task::factory(100)->create();

        // Create test Users
        User::create(['name' => 'man1', 'email' => 'man1@stnzn.ru', 'password' => Hash::make('123123'), 'role_id' => Role::where('name', 'user')->first()->id]);
        User::create(['name' => 'man2', 'email' => 'man2@stnzn.ru', 'password' => Hash::make('123123'), 'role_id' => Role::where('name', 'user')->first()->id]);
        User::create(['name' => 'hod', 'email' => 'hod@stnzn.ru', 'password' => Hash::make('123123'), 'role_id' => Role::where('name', 'head-of-department')->first()->id]);

    }
}

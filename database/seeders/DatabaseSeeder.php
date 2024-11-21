<?php

namespace Database\Seeders;

use App\Models\Contractor;
use App\Models\Department;
use App\Models\Role;
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
        User::factory()->create([
            'name' => 'admin',
            'email' => 'yakimec@stnzn.ru',
            'password' => Hash::make('Redandwhite1'),
            'role_id' => Role::where('name', 'admin')->first()->id
        ]);
    }
}

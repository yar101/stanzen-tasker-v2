<?php

namespace Database\Seeders;

use App\Imports\ContractorsImport;
use App\Models\Contractor;
use App\Models\Department;
use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Contractors

        // null contractor
        Contractor::create(['name' => 'Без контрагента']);

        // export contractors
//        Excel::import(new ContractorsImport, 'contractors.xlsx');


        // Create Departments
        Department::create(['name' => 'Без отдела']);
        Department::create(['name' => 'Инструменты']);

        // Create Roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
        Role::create(['name' => 'head-of-department']);

        // Create Admin
        User::create([
            'name' => 'Александр Якимец',
            'email' => 'yakimec@stnzn.ru',
            'password' => Hash::make('Redandwhite1'),
            'role_id' => Role::where('name', 'admin')->first()->id
        ]);

        // Create Users
        $userRole = Role::where('name', 'user')->first()->id;
        $headOfDepartmentRole = Role::where('name', 'head-of-department')->first()->id;
        $toolsDepartment = Department::where('name', 'Инструменты')->first()->id;
        // Инструменты
        User::create([
            'name' => 'Евгений Голосных',
            'email' => 'golosnykh@stnzn.ru',
            'password' => Hash::make('0000'),
            'role_id' => $headOfDepartmentRole,
            'department_id' => $toolsDepartment,
        ]);

        User::create([
            'name' => 'Станислав Едигарев',
            'email' => 'edigarev@stnzn.ru',
            'password' => Hash::make('0000'),
            'role_id' => $userRole,
            'department_id' => $toolsDepartment,
        ]);

        User::create([
            'name' => 'Алексей Ошов',
            'email' => 'oshov@stnzn.ru',
            'password' => Hash::make('0000'),
            'role_id' => $userRole,
            'department_id' => $toolsDepartment,
        ]);

        User::create([
            'name' => 'Алексей Костиков',
            'email' => 'kostikov@stnzn.ru',
            'password' => Hash::make('0000'),
            'role_id' => $userRole,
            'department_id' => $toolsDepartment,
        ]);

        User::create([
            'name' => 'Илья Трефилов',
            'email' => 'trefilov@stnzn.ru',
            'password' => Hash::make('0000'),
            'role_id' => $userRole,
            'department_id' => $toolsDepartment,
        ]);

        User::create([
            'name' => 'Игорь Кремляков',
            'email' => 'kremlyakov@stnzn.ru',
            'password' => Hash::make('0000'),
            'role_id' => $userRole,
            'department_id' => $toolsDepartment,
        ]);


        // Create Statuses

        // Инструменты
        Status::create(['name' => 'NOT STARTED', 'department_id' => Department::where('name', 'Инструменты')->first()->id]);
        Status::create(['name' => 'ONGOING', 'department_id' => Department::where('name', 'Инструменты')->first()->id]);
        Status::create(['name' => 'ON HOLD', 'department_id' => Department::where('name', 'Инструменты')->first()->id]);
        Status::create(['name' => 'DELAY', 'department_id' => Department::where('name', 'Инструменты')->first()->id]);
        Status::create(['name' => 'DONE', 'department_id' => Department::where('name', 'Инструменты')->first()->id]);
    }
}

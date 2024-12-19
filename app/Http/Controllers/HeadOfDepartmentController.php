<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HeadOfDepartmentController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        $users = User::where('department_id', '=', $currentUser->department->id)
            ->whereRelation('role', 'name', '=', 'user')
            ->orderBy('name')
            ->get();
        $headOfDepartmentUsers = User::where('department_id', '=', $currentUser->department->id)
            ->whereRelation('role', 'name', '=', 'head-of-department')
            ->orderBy('name')
            ->get();
        return Inertia::render('HeadOfDepartmentFunctions/Index', [
            'users' => $users,
            'headOfDepartmentUsers' => $headOfDepartmentUsers,
        ]);
    }
}

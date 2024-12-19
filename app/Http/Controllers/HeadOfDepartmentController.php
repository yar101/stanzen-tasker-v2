<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
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

    public function switchUserRoleToHeadOfDepartment(User $user)
    {
        $HeadOfDepartmentRoleId = Role::where('name', '=', 'head-of-department')->first()->id;
        $user->update(['role_id' => $HeadOfDepartmentRoleId]);
        return redirect()->route('hod-functions');
    }

    public function switchUserRoleToUser(User $user)
    {
        $userRoleId = Role::where('name', '=', 'user')->first()->id;
        $user->update(['role_id' => $userRoleId]);
        return redirect()->route('hod-functions');
    }
}

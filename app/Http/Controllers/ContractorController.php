<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ContractorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contractors = Contractor::orderBy('name')->where('id', '!=', '1')->get();
        $currentUserRole = auth()->user()->role->name;

        return Inertia::render('Contractor/Index', ['contractors' => $contractors, 'currentUserRole' => $currentUserRole]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Contractor::create($request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('contractors', 'name')]]));

        return redirect()->route('contractors.index')->with('success', 'Контрагент добавлен.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contractor $contractor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contractor $contractor)
    {
        $contractor->update($request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('contractors', 'name')->ignore($contractor->id)]]));

        return redirect()->route('contractors.index')->with('success', 'Контрагент обновлён.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contractor $contractor)
    {
        try {
            $contractor = Contractor::findOrFail($contractor->id);
            $contractor->delete();

            return redirect()->route('contractors.index')->with('success', 'Контрагент успешно удалён.');
        } catch (QueryException $e) {
            if ($e->getCode() == '23000') {
                // Ошибка, если контрагент используется в задачах
                return redirect()->route('contractors.index')->with('error', 'Невозможно удалить контрагента. Он используется в задачах.');
            }

            return redirect()->route('contractors.index')->with('error', 'Произошла ошибка при удалении контрагента.');
        }
        //        $contractor->delete();
        //        return redirect()->route('contractors.index')->with('success', 'Контрагент удален.');
    }
}

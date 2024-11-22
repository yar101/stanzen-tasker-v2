<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
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
        $contractors = Contractor::orderByDesc('created_at')->where('id', '!=', '1')->get();

        return Inertia::render('Contractor/Index', [
            'contractors' => $contractors,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Contractor::create(
            $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('contractors', 'name')],
            ])
        );
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
        $contractor->update(
            $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('contractors', 'name')->ignore($contractor->id)],
            ])
        );
        return redirect()->route('contractors.index')->with('success', 'Контрагент добавлен.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contractor $contractor)
    {
        $contractor->delete();
        return redirect()->route('contractors.index')->with('success', 'Контрагент удален.');
    }
}

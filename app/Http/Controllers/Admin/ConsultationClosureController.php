<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultationClosure;
use Illuminate\Http\Request;

class ConsultationClosureController extends Controller
{
    public function index()
    {
        $closures = ConsultationClosure::orderByDesc('starts_at')->get();

        return view('admin.closures.index', compact('closures'));
    }

    public function create()
    {
        return view('admin.closures.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateClosure($request);

        ConsultationClosure::create($validated);

        return redirect()
            ->route('admin.closures.index')
            ->with('success', 'Периодът на затваряне беше добавен.');
    }

    public function edit(ConsultationClosure $closure)
    {
        return view('admin.closures.edit', compact('closure'));
    }

    public function update(Request $request, ConsultationClosure $closure)
    {
        $validated = $this->validateClosure($request);

        $closure->update($validated);

        return redirect()
            ->route('admin.closures.index')
            ->with('success', 'Периодът беше обновен.');
    }

    public function destroy(ConsultationClosure $closure)
    {
        $closure->delete();

        return redirect()
            ->route('admin.closures.index')
            ->with('success', 'Периодът беше изтрит.');
    }

    private function validateClosure(Request $request): array
    {
        return $request->validate([
            'starts_at' => ['required', 'date', 'before:ends_at'],
            'ends_at'   => ['required', 'date', 'after:starts_at'],
            'reason'    => ['nullable', 'string', 'max:500'],
        ], [
            'starts_at.required' => 'Начална дата е задължителна.',
            'ends_at.required'   => 'Крайна дата е задължителна.',
            'starts_at.before'   => 'Началото трябва да е преди края.',
            'ends_at.after'      => 'Краят трябва да е след началото.',
        ]);
    }
}

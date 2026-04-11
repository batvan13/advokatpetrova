<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultationWorkingHours;
use Illuminate\Http\Request;

class ConsultationWorkingHoursController extends Controller
{
    public function index()
    {
        $hours = ConsultationWorkingHours::orderBy('day_of_week')->get();

        return view('admin.working-hours.index', compact('hours'));
    }

    public function edit(ConsultationWorkingHours $workingHour)
    {
        return view('admin.working-hours.edit', compact('workingHour'));
    }

    public function update(Request $request, ConsultationWorkingHours $workingHour)
    {
        $isOpen = (bool) $request->input('is_open', false);

        $rules = [
            'is_open' => ['boolean'],
        ];

        if ($isOpen) {
            $rules['start_time'] = ['required', 'date_format:H:i'];
            $rules['end_time']   = ['required', 'date_format:H:i', 'after:start_time'];
        } else {
            $rules['start_time'] = ['nullable', 'date_format:H:i'];
            $rules['end_time']   = ['nullable', 'date_format:H:i'];
        }

        $validated = $request->validate($rules, [
            'start_time.required'    => 'Начален час е задължителен за отворен ден.',
            'end_time.required'      => 'Краен час е задължителен за отворен ден.',
            'end_time.after'         => 'Краят на работния ден трябва да е след началото.',
            'start_time.date_format' => 'Форматът трябва да е ЧЧ:ММ.',
            'end_time.date_format'   => 'Форматът трябва да е ЧЧ:ММ.',
        ]);

        $validated['is_open'] = $isOpen;

        if (! $isOpen) {
            $validated['start_time'] = null;
            $validated['end_time']   = null;
        }

        $workingHour->update($validated);

        return redirect()
            ->route('admin.working-hours.index')
            ->with('success', 'Работното време за ' . $workingHour->dayLabel() . ' беше обновено.');
    }
}

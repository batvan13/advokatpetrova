<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use Illuminate\Http\Request;

class ConsultationServiceController extends Controller
{
    public function index()
    {
        $services = ConsultationService::query()
            ->orderByRaw("FIELD(type, 'phone', 'chat', 'written', 'video')")
            ->get()
            ->keyBy('type');

        return view('admin.consultation-services.index', compact('services'));
    }

    public function edit(ConsultationService $consultationService)
    {
        return view('admin.consultation-services.edit', [
            'service' => $consultationService,
        ]);
    }

    public function update(Request $request, ConsultationService $consultationService)
    {
        $isVideo = $consultationService->type === 'video';

        $rules = [
            'price_eur'      => ['required', 'numeric', 'min:0'],
            'price_bgn'      => ['required', 'numeric', 'min:0'],
            'show_bgn_price' => ['boolean'],
        ];

        if ($isVideo) {
            $rules['price_eur_60'] = ['required', 'numeric', 'min:0'];
            $rules['price_bgn_60'] = ['required', 'numeric', 'min:0'];
        }

        $validated = $request->validate($rules);

        $validated['show_bgn_price'] = (bool) ($request->input('show_bgn_price', false));

        if (! $isVideo) {
            $validated['price_eur_60'] = null;
            $validated['price_bgn_60'] = null;
        }

        $consultationService->update($validated);

        return redirect()
            ->route('admin.consultation-services.index')
            ->with('success', 'Цените бяха обновени успешно.');
    }
}

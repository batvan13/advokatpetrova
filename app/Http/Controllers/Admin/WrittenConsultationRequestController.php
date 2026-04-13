<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WrittenConsultationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WrittenConsultationRequestController extends Controller
{
    public function index()
    {
        $requests = WrittenConsultationRequest::query()
            ->whereNull('archived_at')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.written-consultations.index', compact('requests'));
    }

    public function show(WrittenConsultationRequest $writtenConsultationRequest)
    {
        $writtenConsultationRequest->load('attachments');

        return view('admin.written-consultations.show', [
            'request' => $writtenConsultationRequest,
        ]);
    }

    public function markAnswered(WrittenConsultationRequest $writtenConsultationRequest)
    {
        $writtenConsultationRequest->update([
            'status' => WrittenConsultationRequest::STATUS_ANSWERED,
        ]);

        return redirect()
            ->route('admin.written-consultations.show', $writtenConsultationRequest)
            ->with('success', 'Заявката е маркирана като отговорена.');
    }

    public function archive(WrittenConsultationRequest $writtenConsultationRequest)
    {
        if ($writtenConsultationRequest->archived_at !== null) {
            return redirect()
                ->route('admin.written-consultations.show', $writtenConsultationRequest)
                ->with('info', 'Заявката е вече архивирана.');
        }

        if ($writtenConsultationRequest->status !== WrittenConsultationRequest::STATUS_ANSWERED) {
            return redirect()
                ->route('admin.written-consultations.show', $writtenConsultationRequest)
                ->with('error', 'Само отговорени заявки могат да бъдат архивирани.');
        }

        $writtenConsultationRequest->update(['archived_at' => now()]);

        return redirect()
            ->route('admin.written-consultations.show', $writtenConsultationRequest)
            ->with('success', 'Заявката е архивирана успешно.');
    }

    public function download(WrittenConsultationRequest $writtenConsultationRequest, int $attachmentId): StreamedResponse
    {
        $attachment = $writtenConsultationRequest->attachments()->findOrFail($attachmentId);

        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download(
            $attachment->path,
            $attachment->original_name
        );
    }
}

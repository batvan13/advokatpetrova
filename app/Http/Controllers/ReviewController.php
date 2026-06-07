<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Review;
use Illuminate\Support\Arr;

class ReviewController extends Controller
{
    public function create()
    {
        return view('pages.review-create');
    }

    public function submit(ReviewRequest $request)
    {
        $payload = Arr::only($request->validated(), [
            'first_name',
            'last_name',
            'email',
            'body',
        ]);

        Review::create([
            ...$payload,
            'status' => Review::STATUS_PENDING,
        ]);

        return redirect()->route('reviews.success');
    }

    public function success()
    {
        return view('pages.review-success');
    }
}

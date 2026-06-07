<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index()
    {
        $status = request('status', 'all');
        $allowed = ['all', Review::STATUS_PENDING, Review::STATUS_PUBLISHED];

        if (! in_array($status, $allowed, true)) {
            $status = 'all';
        }

        $query = Review::query()->ordered();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $reviews = $query->paginate(20)->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'filter'  => $status,
        ]);
    }

    public function show(Review $review)
    {
        return view('admin.reviews.show', compact('review'));
    }

    public function publish(Review $review)
    {
        $review->update(['status' => Review::STATUS_PUBLISHED]);

        return redirect()
            ->route('admin.reviews.show', $review)
            ->with('success', 'Отзивът е публикуван.');
    }

    public function destroy(Review $review)
    {
        $name = $review->fullName();
        $review->delete();

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Отзивът на „'.$name.'“ беше изтрит.');
    }
}

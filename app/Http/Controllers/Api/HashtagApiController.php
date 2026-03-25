<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hashtag;
use Illuminate\Http\Request;

class HashtagApiController extends Controller
{
    /**
     * Get hashtag suggestions for autocomplete
     * - When search is empty (just # typed): return top 5 hashtags
     * - When search has letters (e.g., #a): return only matching hashtags (no top, no recent)
     */
    public function suggestions(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $limit = (int) $request->get('limit', 10);

            // If search is empty, return only top 5 hashtags
            if (empty($search)) {
                $topHashtags = Hashtag::orderBy('usage_count', 'desc')
                    ->limit(5)
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'top' => $topHashtags->map(fn($h) => [
                            'id' => $h->id,
                            'name' => $h->name,
                            'slug' => $h->slug,
                            'usage_count' => $h->usage_count,
                        ]),
                        'matching' => []
                    ]
                ]);
            }

            // If search has letters, return only matching hashtags
            $matchingHashtags = Hashtag::where('name', 'LIKE', $search . '%')
                ->orderBy('usage_count', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'top' => [],
                    'matching' => $matchingHashtags->map(fn($h) => [
                        'id' => $h->id,
                        'name' => $h->name,
                        'slug' => $h->slug,
                        'usage_count' => $h->usage_count,
                    ])
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Hashtag suggestions error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

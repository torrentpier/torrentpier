<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emoji\IndexEmojiRequest;
use App\Http\Requests\Emoji\SearchEmojiRequest;
use App\Http\Requests\Emoji\StoreEmojiRequest;
use App\Http\Requests\Emoji\UpdateEmojiRequest;
use App\Http\Resources\EmojiResource;
use App\Models\Emoji;
use Illuminate\Http\Request;

class EmojiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexEmojiRequest $request)
    {
        $emojis = Emoji::query()
            ->when($request->get('category_id'), function ($query, $categoryId) {
                $query->where('emoji_category_id', $categoryId);
            })
            ->when($request->get('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('emoji_shortcode', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('emoji_text', 'like', "%{$search}%");
                });
            })
            ->when($request->get('with_category'), function ($query) {
                $query->with('category');
            })
            ->when($request->get('with_aliases'), function ($query) {
                $query->with('aliases');
            })
            ->orderBy('display_order')
            ->paginate($request->get('per_page', 50));

        return EmojiResource::collection($emojis);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmojiRequest $request)
    {
        $emoji = Emoji::create($request->validated());

        if ($request->get('with_category')) {
            $emoji->load('category');
        }

        return new EmojiResource($emoji);
    }

    /**
     * Display the specified resource.
     */
    public function show(Emoji $emoji, Request $request)
    {
        $emoji->load([
            'category',
            'aliases' => function ($query) {
                $query->orderBy('alias');
            },
        ]);

        return new EmojiResource($emoji);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmojiRequest $request, Emoji $emoji)
    {
        $emoji->update($request->validated());

        if ($request->get('with_category')) {
            $emoji->load('category');
        }

        return new EmojiResource($emoji);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Emoji $emoji)
    {
        $emoji->delete();

        return response()->json(null, 204);
    }

    /**
     * Search emojis using Laravel Scout.
     */
    public function search(SearchEmojiRequest $request)
    {
        $emojis = Emoji::search($request->get('q'))
            ->take((int) $request->get('limit', 20))
            ->get();

        // Load relationships if requested
        $emojis->load(array_filter([
            $request->get('with_category') ? 'category' : null,
            $request->get('with_aliases') ? 'aliases' : null,
        ]));

        return EmojiResource::collection($emojis);
    }
}

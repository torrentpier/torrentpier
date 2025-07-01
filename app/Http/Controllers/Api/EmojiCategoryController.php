<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emoji\StoreEmojiCategoryRequest;
use App\Http\Requests\Emoji\UpdateEmojiCategoryRequest;
use App\Http\Resources\EmojiCategoryResource;
use App\Models\EmojiCategory;
use Illuminate\Http\Request;

class EmojiCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = EmojiCategory::query()
            ->when($request->get('with_emojis'), function ($query) {
                $query->withCount('emojis');
            })
            ->when($request->get('include_emojis'), function ($query) {
                $query->with(['emojis' => function ($q) {
                    $q->orderBy('display_order');
                }]);
            })
            ->orderBy('display_order')
            ->get();

        return EmojiCategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmojiCategoryRequest $request)
    {
        $category = EmojiCategory::create($request->validated());

        return new EmojiCategoryResource($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(EmojiCategory $emojiCategory, Request $request)
    {
        $emojiCategory->load([
            'emojis' => function ($query) use ($request) {
                $query->orderBy('display_order');
                if ($request->get('with_aliases')) {
                    $query->with('aliases');
                }
            },
        ]);

        return new EmojiCategoryResource($emojiCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmojiCategoryRequest $request, EmojiCategory $emojiCategory)
    {
        $emojiCategory->update($request->validated());

        return new EmojiCategoryResource($emojiCategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmojiCategory $emojiCategory)
    {
        $emojiCategory->delete();

        return response()->json(null, 204);
    }
}

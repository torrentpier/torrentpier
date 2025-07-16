<?php

namespace App\Http\Controllers\Admin\Emoji;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emoji\StoreEmojiCategoryRequest;
use App\Http\Requests\Emoji\UpdateEmojiCategoryRequest;
use App\Models\EmojiCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmojiCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = EmojiCategory::withCount('emojis')
            ->orderBy('display_order')
            ->get();

        return response()->json($categories);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not needed for Inertia - handled in frontend
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmojiCategoryRequest $request): JsonResponse
    {
        $category = EmojiCategory::create($request->validated());

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => $category,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(EmojiCategory $category)
    {
        return response()->json($category->load('emojis'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmojiCategory $category)
    {
        // Not needed for Inertia - handled in frontend
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmojiCategoryRequest $request, EmojiCategory $category): JsonResponse
    {
        $category->update($request->validated());

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $category,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmojiCategory $category): JsonResponse
    {
        if ($category->emojis()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with existing emojis.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }
}

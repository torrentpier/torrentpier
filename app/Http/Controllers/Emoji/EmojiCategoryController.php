<?php

namespace App\Http\Controllers\Emoji;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emoji\StoreEmojiCategoryRequest;
use App\Http\Requests\Emoji\UpdateEmojiCategoryRequest;
use App\Models\EmojiCategory;

class EmojiCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmojiCategoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(EmojiCategory $emojiCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmojiCategory $emojiCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmojiCategoryRequest $request, EmojiCategory $emojiCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmojiCategory $emojiCategory)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\Emoji;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emoji\StoreEmojiRequest;
use App\Http\Requests\Emoji\UpdateEmojiRequest;
use App\Models\Emoji;

class EmojiController extends Controller
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
    public function store(StoreEmojiRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Emoji $emoji)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Emoji $emoji)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmojiRequest $request, Emoji $emoji)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Emoji $emoji)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\Admin\Emoji;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emoji\StoreEmojiAliasRequest;
use App\Http\Requests\Emoji\UpdateEmojiAliasRequest;
use App\Models\EmojiAlias;
use Illuminate\Http\RedirectResponse;

class EmojiAliasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Not needed - aliases are loaded with emojis
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
    public function store(StoreEmojiAliasRequest $request): RedirectResponse
    {
        $alias = EmojiAlias::create($request->validated());

        return back()->with('success', 'Alias added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmojiAlias $alias)
    {
        // Not needed for Inertia - handled in frontend
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmojiAlias $alias)
    {
        // Not needed for Inertia - handled in frontend
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmojiAliasRequest $request, EmojiAlias $alias): RedirectResponse
    {
        $alias->update($request->validated());

        return back()->with('success', 'Alias updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmojiAlias $alias): RedirectResponse
    {
        $alias->delete();

        return back()->with('success', 'Alias removed successfully.');
    }
}

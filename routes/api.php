<?php

use App\Http\Controllers\Api\EmojiAliasController;
use App\Http\Controllers\Api\EmojiCategoryController;
use App\Http\Controllers\Api\EmojiController;
use App\Http\Controllers\Api\WordFilterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Emoji API Routes
Route::prefix('emoji')->group(function () {
    // Emoji - search routes must come before resource routes
    Route::get('emojis/search', [EmojiController::class, 'search'])->name('emojis.search');
    Route::apiResource('emojis', EmojiController::class);

    // Emoji Aliases - search routes must come before resource routes
    Route::get('aliases/search', [EmojiAliasController::class, 'search'])->name('aliases.search');
    Route::apiResource('aliases', EmojiAliasController::class);

    // Emoji Categories
    Route::apiResource('categories', EmojiCategoryController::class);
});

// Word Filter API Routes
Route::prefix('word-filters')->group(function () {
    // Search route must come before resource routes
    Route::get('search', [WordFilterController::class, 'search'])->name('word-filters.search');
    Route::apiResource('/', WordFilterController::class)->parameters(['' => 'filter']);
});

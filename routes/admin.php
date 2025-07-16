<?php

use App\Http\Controllers\Admin\Emoji\EmojiAliasController;
use App\Http\Controllers\Admin\Emoji\EmojiCategoryController;
use App\Http\Controllers\Admin\Emoji\EmojiController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::get('admin', function () {
        return Inertia::render('admin/index');
    })->name('admin.index');

    // Emoji management routes
    Route::prefix('admin/emojis')->group(function () {
        Route::get('/', [EmojiController::class, 'index'])->name('admin.emojis.index');
        Route::get('/create', [EmojiController::class, 'create'])->name('admin.emojis.create');
        Route::post('/', [EmojiController::class, 'store'])->name('admin.emojis.store');
        Route::get('/{emoji}/edit', [EmojiController::class, 'edit'])->name('admin.emojis.edit');
        Route::patch('/{emoji}', [EmojiController::class, 'update'])->name('admin.emojis.update');
        Route::delete('/{emoji}', [EmojiController::class, 'destroy'])->name('admin.emojis.destroy');

        // Category management (AJAX endpoints)
        Route::post('/categories', [EmojiCategoryController::class, 'store'])->name('admin.emoji-categories.store');
        Route::patch('/categories/{category}', [EmojiCategoryController::class, 'update'])->name('admin.emoji-categories.update');
        Route::delete('/categories/{category}', [EmojiCategoryController::class, 'destroy'])->name('admin.emoji-categories.destroy');

        // Alias management (AJAX endpoints)
        Route::post('/aliases', [EmojiAliasController::class, 'store'])->name('admin.emoji-aliases.store');
        Route::patch('/aliases/{alias}', [EmojiAliasController::class, 'update'])->name('admin.emoji-aliases.update');
        Route::delete('/aliases/{alias}', [EmojiAliasController::class, 'destroy'])->name('admin.emoji-aliases.destroy');
    });
});

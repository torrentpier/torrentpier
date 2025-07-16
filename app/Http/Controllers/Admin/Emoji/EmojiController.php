<?php

namespace App\Http\Controllers\Admin\Emoji;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emoji\StoreEmojiRequest;
use App\Http\Requests\Emoji\UpdateEmojiRequest;
use App\Models\Emoji;
use App\Models\EmojiCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmojiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Emoji::with(['category', 'aliases'])
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('emoji_shortcode', 'like', "%{$search}%")
                    ->orWhere('emoji_text', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($categoryId = $request->get('category_id')) {
            $query->where('emoji_category_id', $categoryId);
        }

        $emojis = $query->paginate(20)->withQueryString();
        $categories = EmojiCategory::withCount('emojis')->orderBy('display_order')->get();

        return Inertia::render('admin/emojis/index', [
            'emojis' => $emojis,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $categories = EmojiCategory::withCount('emojis')->orderBy('display_order')->get();

        return Inertia::render('admin/emojis/form', [
            'categories' => $categories,
            'emoji' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmojiRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('emojis', 'public');
            $data['image_url'] = $path;
        }

        // Set sprite_mode based on sprite_params
        if (isset($data['sprite_params']) && !empty($data['sprite_params'])) {
            $data['sprite_mode'] = true;
        }

        Emoji::create($data);

        return redirect()->route('admin.emojis.index')
            ->with('success', 'Emoji created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Emoji $emoji): Response
    {
        return redirect()->route('admin.emojis.edit', $emoji);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Emoji $emoji): Response
    {
        $categories = EmojiCategory::withCount('emojis')->orderBy('display_order')->get();
        $emoji->load('aliases');

        return Inertia::render('admin/emojis/form', [
            'categories' => $categories,
            'emoji' => $emoji,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmojiRequest $request, Emoji $emoji): RedirectResponse
    {
        $data = $request->validated();

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('emojis', 'public');
            $data['image_url'] = $path;
        }

        // Set sprite_mode based on sprite_params
        if (isset($data['sprite_params']) && !empty($data['sprite_params'])) {
            $data['sprite_mode'] = true;
        }

        $emoji->update($data);

        return redirect()->route('admin.emojis.index')
            ->with('success', 'Emoji updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Emoji $emoji): RedirectResponse
    {
        $emoji->delete();

        return redirect()->route('admin.emojis.index')
            ->with('success', 'Emoji deleted successfully.');
    }
}

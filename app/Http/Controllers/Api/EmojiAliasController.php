<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Emoji\SearchEmojiAliasRequest;
use App\Http\Requests\Emoji\StoreEmojiAliasRequest;
use App\Http\Requests\Emoji\UpdateEmojiAliasRequest;
use App\Http\Resources\EmojiAliasResource;
use App\Models\EmojiAlias;
use Illuminate\Http\Request;

class EmojiAliasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $aliases = EmojiAlias::query()
            ->when($request->get('emoji_id'), function ($query, $emojiId) {
                $query->where('emoji_id', $emojiId);
            })
            ->when($request->get('search'), function ($query, $search) {
                $query->where('alias', 'like', "%{$search}%");
            })
            ->when($request->get('with_emoji'), function ($query) {
                $query->with(['emoji' => function ($q) {
                    $q->with('category');
                }]);
            })
            ->orderBy('alias')
            ->paginate($request->get('per_page', 50));

        return EmojiAliasResource::collection($aliases);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmojiAliasRequest $request)
    {
        $alias = EmojiAlias::create($request->validated());

        if ($request->get('with_emoji')) {
            $alias->load(['emoji' => function ($query) {
                $query->with('category');
            }]);
        }

        return new EmojiAliasResource($alias);
    }

    /**
     * Display the specified resource.
     */
    public function show(EmojiAlias $alias, Request $request)
    {
        $alias->load(['emoji' => function ($query) {
            $query->with('category');
        }]);

        return new EmojiAliasResource($alias);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmojiAliasRequest $request, EmojiAlias $alias)
    {
        $alias->update($request->validated());

        if ($request->get('with_emoji')) {
            $alias->load(['emoji' => function ($query) {
                $query->with('category');
            }]);
        }

        return new EmojiAliasResource($alias);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmojiAlias $alias)
    {
        $alias->delete();

        return response()->json(null, 204);
    }

    /**
     * Search aliases using Laravel Scout.
     */
    public function search(SearchEmojiAliasRequest $request)
    {
        $aliases = EmojiAlias::search($request->get('q'))
            ->take($request->get('limit', 20))
            ->get();

        // Load emoji relationships if requested
        if ($request->get('with_emoji')) {
            $aliases->load(['emoji' => function ($query) {
                $query->with('category');
            }]);
        }

        return EmojiAliasResource::collection($aliases);
    }
}

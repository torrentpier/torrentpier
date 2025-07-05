<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WordFilter\IndexWordFilterRequest;
use App\Http\Requests\WordFilter\SearchWordFilterRequest;
use App\Http\Requests\WordFilter\StoreWordFilterRequest;
use App\Http\Requests\WordFilter\UpdateWordFilterRequest;
use App\Http\Resources\WordFilterResource;
use App\Models\WordFilter;
use Illuminate\Http\Request;

class WordFilterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexWordFilterRequest $request)
    {
        $filters = WordFilter::query()
            ->when($request->get('filter_type'), function ($query, $filterType) {
                $query->where('filter_type', $filterType);
            })
            ->when($request->get('pattern_type'), function ($query, $patternType) {
                $query->where('pattern_type', $patternType);
            })
            ->when($request->get('severity'), function ($query, $severity) {
                $query->where('severity', $severity);
            })
            ->when($request->has('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            })
            ->when($request->get('applies_to'), function ($query, $appliesTo) {
                $query->whereJsonContains('applies_to', $appliesTo);
            })
            ->when($request->get('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('pattern', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($request->get('with_creator'), function ($query) {
                $query->with('creator');
            })
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
            ->paginate($request->get('per_page', 50));

        return WordFilterResource::collection($filters);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWordFilterRequest $request)
    {
        $filter = WordFilter::create($request->validated());

        if ($request->get('with_creator')) {
            $filter->load('creator');
        }

        return new WordFilterResource($filter);
    }

    /**
     * Display the specified resource.
     */
    public function show(WordFilter $filter, Request $request)
    {
        if ($request->get('with_creator')) {
            $filter->load('creator');
        }

        return new WordFilterResource($filter);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWordFilterRequest $request, WordFilter $filter)
    {
        $filter->update($request->validated());

        if ($request->get('with_creator')) {
            $filter->load('creator');
        }

        return new WordFilterResource($filter);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WordFilter $filter)
    {
        $filter->delete();

        return response()->json(null, 204);
    }

    /**
     * Search word filters using Laravel Scout.
     */
    public function search(SearchWordFilterRequest $request)
    {
        $filters = WordFilter::search($request->get('q'))
            ->take((int) $request->get('limit', 20))
            ->get();

        // Apply additional filters after search
        if ($request->get('filter_type')) {
            $filters = $filters->where('filter_type', $request->get('filter_type'));
        }

        if ($request->get('severity')) {
            $filters = $filters->where('severity', $request->get('severity'));
        }

        if ($request->has('is_active')) {
            $filters = $filters->where('is_active', $request->boolean('is_active'));
        }

        // Load relationships if requested
        if ($request->get('with_creator')) {
            $filters->load('creator');
        }

        return WordFilterResource::collection($filters);
    }
}

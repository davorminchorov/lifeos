<?php

namespace App\Subscriptions\UI\Api;

use App\Models\SubscriptionTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    /**
     * Display a listing of the tags.
     */
    public function index(): JsonResponse
    {
        $tags = SubscriptionTag::withCount('subscriptions')
            ->orderBy('name')
            ->get();

        return response()->json($tags);
    }

    /**
     * Store a newly created tag.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:subscription_tags,name',
            'color' => 'required|string|size:7|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $tag = SubscriptionTag::create([
            'name' => $request->input('name'),
            'color' => $request->input('color'),
        ]);

        return response()->json($tag, Response::HTTP_CREATED);
    }

    /**
     * Display the specified tag.
     */
    public function show(string $id): JsonResponse
    {
        $tag = SubscriptionTag::withCount('subscriptions')->findOrFail($id);

        return response()->json($tag);
    }

    /**
     * Update the specified tag.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $tag = SubscriptionTag::findOrFail($id);

        $request->validate([
            'name' => "required|string|max:50|unique:subscription_tags,name,{$id},id",
            'color' => 'required|string|size:7|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $tag->update([
            'name' => $request->input('name'),
            'color' => $request->input('color'),
        ]);

        return response()->json($tag);
    }

    /**
     * Remove the specified tag.
     */
    public function destroy(string $id): JsonResponse
    {
        $tag = SubscriptionTag::findOrFail($id);
        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully']);
    }

    /**
     * Attach a tag to a subscription.
     */
    public function attachTag(Request $request): JsonResponse
    {
        $request->validate([
            'subscription_id' => 'required|uuid|exists:subscriptions,id',
            'tag_id' => 'required|uuid|exists:subscription_tags,id',
        ]);

        // Check if the relationship already exists
        $exists = DB::table('subscription_tag')
            ->where('subscription_id', $request->input('subscription_id'))
            ->where('tag_id', $request->input('tag_id'))
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Tag is already attached to this subscription']);
        }

        // Create the relationship
        DB::table('subscription_tag')->insert([
            'subscription_id' => $request->input('subscription_id'),
            'tag_id' => $request->input('tag_id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Tag attached successfully']);
    }

    /**
     * Detach a tag from a subscription.
     */
    public function detachTag(Request $request): JsonResponse
    {
        $request->validate([
            'subscription_id' => 'required|uuid|exists:subscriptions,id',
            'tag_id' => 'required|uuid|exists:subscription_tags,id',
        ]);

        // Delete the relationship
        $deleted = DB::table('subscription_tag')
            ->where('subscription_id', $request->input('subscription_id'))
            ->where('tag_id', $request->input('tag_id'))
            ->delete();

        if (!$deleted) {
            return response()->json(
                ['message' => 'Tag is not attached to this subscription'],
                Response::HTTP_BAD_REQUEST
            );
        }

        return response()->json(['message' => 'Tag detached successfully']);
    }

    /**
     * Get all tags for a subscription.
     */
    public function getSubscriptionTags(string $subscriptionId): JsonResponse
    {
        $tags = DB::table('subscription_tags')
            ->join('subscription_tag', 'subscription_tags.id', '=', 'subscription_tag.tag_id')
            ->where('subscription_tag.subscription_id', $subscriptionId)
            ->select('subscription_tags.*')
            ->get();

        return response()->json($tags);
    }
}

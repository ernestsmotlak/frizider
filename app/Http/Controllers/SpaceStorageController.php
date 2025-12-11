<?php

namespace App\Http\Controllers;

use App\Models\SpaceStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageFactoryInterface;

class SpaceStorageController extends Controller
{
    protected function user()
    {
        return auth('api')->user();
    }

    public function index()
    {
        $spaces = SpaceStorage::where('user_id', $this->user()->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $spaces
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $space = $this->user()->spaceStorages()->create($validated);

        return response()->json([
            'message' => 'Storage space created.',
            'data' => $space
        ], 201);
    }

    public function show(string $id)
    {
        $space = SpaceStorage::findOrFail($id);

        if ($space->user_id !== $this->user()->id) {
            return response()->json([
                'message' => 'You do not have permission to view this storage space.',
            ], 403);
        }

        return response()->json([
            'data' => $space,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $spaceStorage = SpaceStorage::findOrFail($id);

        if ($spaceStorage->user_id !== $this->user()->id) {
            return response()->json([
                'message' => 'You do not have permission to update this storage space.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $spaceStorage->update($validated);

        return response()->json([
            'message' => 'Storage space updated successfully.',
            'data' => $spaceStorage->fresh()
        ]);
    }


    public function destroy(string $id)
    {
        $storageSpace = SpaceStorage::findOrFail($id);

        if ($storageSpace->user_id !== $this->user()->id) {
            return response()->json([
                'message' => 'You do not have permission to delete this storage space.',
            ], 403);
        }

        $storageSpace->delete();

        return response()->json([
            'message' => 'Storage space deleted successfully.'
        ], 200);
    }
}

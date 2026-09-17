<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    /**
     * Menampilkan daftar inventaris.
     */
    public function index(Request $request)
    {
        $query = Inventory::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('inventory_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filter category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter condition
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter location
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        $inventories = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Data inventaris berhasil diambil.',
            'data' => $inventories,
        ]);
    }

    /**
     * Menyimpan inventaris baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_code' => [
                'required',
                'string',
                'max:100',
                'unique:inventories,inventory_code',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'condition' => [
                'required',
                Rule::in([
                    'good',
                    'minor_damage',
                    'major_damage',
                    'broken',
                ]),
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                Rule::in([
                    'available',
                    'in_use',
                    'maintenance',
                    'unavailable',
                ]),
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        // Upload gambar
        if ($request->hasFile('image')) {
            $validated['image'] = $request
                ->file('image')
                ->store('inventories', 'public');
        }

        $inventory = Inventory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inventaris berhasil ditambahkan.',
            'data' => $inventory,
        ], 201);
    }

    /**
     * Menampilkan detail inventaris.
     */
    public function show(Inventory $inventory)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail inventaris berhasil diambil.',
            'data' => $inventory,
        ]);
    }

    /**
     * Mengubah data inventaris.
     */
    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'inventory_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('inventories', 'inventory_code')
                    ->ignore($inventory->id),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'condition' => [
                'required',
                Rule::in([
                    'good',
                    'minor_damage',
                    'major_damage',
                    'broken',
                ]),
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                Rule::in([
                    'available',
                    'in_use',
                    'maintenance',
                    'unavailable',
                ]),
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        // Upload gambar baru
        if ($request->hasFile('image')) {

            // Hapus gambar lama
            if (
                $inventory->image &&
                Storage::disk('public')->exists($inventory->image)
            ) {
                Storage::disk('public')->delete($inventory->image);
            }

            $validated['image'] = $request
                ->file('image')
                ->store('inventories', 'public');
        }

        $inventory->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inventaris berhasil diperbarui.',
            'data' => $inventory->fresh(),
        ]);
    }

    /**
     * Menghapus inventaris.
     */
    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inventaris berhasil dihapus.',
        ]);
    }
}
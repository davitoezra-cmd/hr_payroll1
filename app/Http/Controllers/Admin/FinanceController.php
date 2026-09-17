<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Finance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FinanceController extends Controller
{
    /**
     * List Finance
     */
    public function index()
    {
        $finances = Finance::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $finances
        ]);
    }

    /**
     * Tambah Finance
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            
            'email' => 'required|email|unique:finances',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:6',
        ]);

        $finance = Finance::create([
            'name' => $request->name,
            
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Finance berhasil ditambahkan.',
            'data' => $finance
        ], 201);
    }

    /**
     * Detail Finance
     */
    public function show($id)
    {
        $finance = Finance::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $finance
        ]);
    }

    /**
     * Update Finance
     */
    public function update(Request $request, $id)
    {
        $finance = Finance::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
           
            'email' => 'required|email|unique:finances,email,' . $finance->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:6',
            'is_active' => 'required|boolean',
        ]);

        $finance->name = $request->name;
        
        $finance->email = $request->email;
        $finance->phone = $request->phone;
        $finance->is_active = $request->is_active;

        if ($request->filled('password')) {
            $finance->password = Hash::make($request->password);
        }

        $finance->save();

        return response()->json([
            'success' => true,
            'message' => 'Data Finance berhasil diperbarui.',
            'data' => $finance
        ]);
    }

    /**
     * Hapus Finance
     */
    public function destroy($id)
    {
        $finance = Finance::findOrFail($id);

        $finance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Finance berhasil dihapus.'
        ]);
    }
}
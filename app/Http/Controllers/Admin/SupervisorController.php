<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SupervisorController extends Controller
{
    public function index()
    {
        $supervisors = Supervisor::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $supervisors
        ]);
    }

    /**
     * 
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            
            'email' => 'required|email|unique:finances',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:6',
        ]);

        $supervisor = Supervisor::create([
            'name' => $request->name,
            
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Supervisor berhasil ditambahkan.',
            'data' => $supervisor
        ], 201);
    }

    /**
     * 
     */
    public function show($id)
    {
        $supervisor = Supervisor::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $finance
        ]);
    }

    /**
     * 
     */
    public function update(Request $request, $id)
    {
        $supervisor = Supervisor::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
           
            'email' => 'required|email|unique:supervisors,email,' . $supervisor->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:6',
            'is_active' => 'required|boolean',
        ]);

        $supervisor->name = $request->name;
        
        $supervisor->email = $request->email;
        $supervisor->phone = $request->phone;
        $supervisor->is_active = $request->is_active;

        if ($request->filled('password')) {
            $supervisor->password = Hash::make($request->password);
        }

        $supervisor->save();

        return response()->json([
            'success' => true,
            'message' => 'Data Supervisor berhasil diperbarui.',
            'data' => $supervisor
        ]);
    }

    /**
     * 
     */
    public function destroy($id)
    {
        $supervisor = Supervisor::findOrFail($id);

        $supervisor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supervisor berhasil dihapus.'
        ]);
    }
}

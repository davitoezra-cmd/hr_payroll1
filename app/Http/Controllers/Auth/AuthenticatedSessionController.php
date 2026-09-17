<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller
{

    /**
     * Halaman Login
     */
    public function create(): View
    {
        return view('auth.login');
    }



    /**
     * Login Super Admin / Employee
     */
    public function store(LoginRequest $request): RedirectResponse
    {

        $credentials = $request->validated();



        // Coba login Super Admin
        if (Auth::guard('user')->attempt($credentials)) {


            $request->session()->regenerate();


            return redirect()
                ->route('admin.dashboard');

        }



        // Coba login Employee
        if (Auth::guard('employee')->attempt($credentials)) {


            $request->session()->regenerate();


            return redirect()
                ->route('employee.dashboard');

        }



        return back()->withErrors([
            'email'=>'Email atau password salah.'
        ]);

    }




    /**
     * Logout
     */
    public function destroy(Request $request): RedirectResponse
    {

        // logout semua guard yang aktif

        Auth::guard('user')->logout();

        Auth::guard('employee')->logout();



        $request->session()->invalidate();

        $request->session()->regenerateToken();



        return redirect('/');

    }

}
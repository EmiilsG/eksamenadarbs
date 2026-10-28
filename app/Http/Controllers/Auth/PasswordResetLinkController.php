<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Paroles atiestatīšanas saite nosūtīta uz jūsu e-pastu.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Neizdevās nosūtīt saiti. Pārbaudiet, vai e-pasts ir reģistrēts.']);
    }
}

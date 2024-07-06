<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class StaffLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        if ($user->hasRole('staff')) {
            return redirect()->route('filament.support.pages.dashboard');
        }

        return redirect()->intended(config('filament.home_url'));
    }
}

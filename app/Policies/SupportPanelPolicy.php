<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupportPanelPolicy
{
    use HandlesAuthorization;

    public function view(User $user)
    {
        return $user->role === 'staff'; // Contoh: Hanya role "staff" yang diizinkan mengakses panel "support"
    }
}

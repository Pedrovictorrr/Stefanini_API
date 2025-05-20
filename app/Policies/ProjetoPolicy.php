<?php

namespace App\Policies;

use App\Models\Projeto;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ProjetoPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Projeto $projeto)
    {
        return $user->id === $projeto->user_id;
    }

    public function update(User $user, Projeto $projeto)
    {
        return $user->id === $projeto->user_id;
    }

    public function delete(User $user, Projeto $projeto)
    {
        return $user->id === $projeto->user_id;
    }
}

<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Nur im Debug Modus dürfen alle User angesehen werden
     *
     * @param User $user Der aktuelle authentifizierte User
     * @return boolean Ob der User alle User anschauen darf
     */
    public function viewAny(User $user): bool
    {
        return config('app.debug');
    }

    /**
     * Alle User können jeden User anschauen
     *
     * @param User $user Der aktuelle authentifizierte User
     * @param User $model Der User der angeschaut werden soll
     * @return boolean Ob der authentifizierte User den User anschauen darf
     */
    public function view(User $user, User $model): bool
    {
        return true;
    }

    /**
     * Jeder darf User erstellen
     *
     * @param User $user Der aktuelle authentifizierte User
     * @return boolean Ob der User einen User ersetellen darf
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Wenn der User sich selbst bearbeitet und der User nicht anonym ist, darf er sich bearbeiten
     *
     * @param User $user Der aktuelle authentifizierte User
     * @param User $model Der zu bearbeitende User
     * @return boolean Ob der authentifizierte User den User bearbeiten darf
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id && !$model->anonymous;
    }

    /**
     * Wenn der User sich selbst löscht, darf er sich löschen
     *
     * @param User $user Der aktuelle authentifizierte User
     * @param User $model Der User der gelöscht werden soll
     * @return boolean Ob der authentifizierte User gelöscht werden darf
     */
    public function delete(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * prüft, ob ein User die User mithilfe des suchstrings suchen darf
     *
     * standardmäßig true
     *
     * @param User $user
     * @return bool
     */
    public function searchAny(User $user, string $searchString): bool
    {
        return true;
    }
}

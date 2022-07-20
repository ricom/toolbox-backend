<?php

namespace App\Policies;

use App\Helper\PermissionHelper;
use App\Models\Save;
use App\Models\SharedSave;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SharedSavePolicy
{
    use HandlesAuthorization;

    /**
     * Nur im Debug Modus dürfen alle SharedSaves anschauen
     *
     * @param User $user Der aktuelle authentifizierte User
     * @return mixed Ob der User alle SharedSaves Einträge anschauen darf
     */
    public function viewAny(User $user)
    {
        return config("app.debug");
    }

    /**
     * Wenn der User mindestens die Leseberechtigung auf den zugehörigen Speicherstand hat, darf er ihn anschauen
     *
     * @param User $user Der aktuelle authentifizierte User
     * @param SharedSave $sharedSave Der zu überprüfende SharedSave Eintrag
     * @return bool Ob der User den SharedSave Eintrag ansehen darf
     */
    public function view(User $user, SharedSave $sharedSave): bool
    {
        return $sharedSave->safe->hasAtLeasPermission($user, PermissionHelper::$PERMISSION_READ);
    }


    /**
     * Wenn der User mindestens die Leseberechtigung auf den Speicherstand hat, darf er ihn anschauen
     *
     * @param User $user Der aktuelle authentifizierte User
     * @param Save $save Der Speicherstand
     * @return bool Ob der User die ShareSaves von dem Speicherstand ansehen darf
     */
    public function viewOfSave(User $user, Save $save): bool
    {
        return $save->hasAtLeasPermission($user, PermissionHelper::$PERMISSION_READ);
    }

    /**
     * Nur der User selbst darf sehen bei welchen Speicherständen standen dieser Mitarbeitet
     *
     * @param User $user Der aktuelle authentifizierte User
     * @param User $model Der angegebene User
     * @return bool Ob der User alle geteilten Speicherständen angucken darf
     */
    public function viewOfUser(User $user, User $model): bool
    {
        return $model->id === $user->id;
    }

    /**
     * Wenn der User Adminrechte auf den Speicherstand hat, darf er eine Einladung erstellen
     *
     * @param User $user Der aktuelle authentifizierte User
     * @param Save $save Der Speicherstand
     * @return bool Ob der User eine Einladung erstellen darf
     */
    public function create(User $user, Save $save): bool
    {
        return $save->hasAtLeasPermission($user, PermissionHelper::$PERMISSION_ADMIN);
    }

    /**
     * Wenn der User Adminrechte auf den zugehörigen Speicherstand hat, darf er eine Einladung bearbeiten
     *
     * @param User $user Der aktuelle authentifizierte User
     * @param SharedSave $sharedSave Die Einladung
     * @return bool Ob der User die Einladung ändern darf
     */
    public function update(User $user, SharedSave $sharedSave): bool
    {
        return $sharedSave->safe->hasAtLeasPermission($user, PermissionHelper::$PERMISSION_ADMIN);
    }


    /**
     * Wenn der User mit der Einladung eingeladen wird, kann er die Einladung annehmen oder ablehnen
     *
     * @param User $user Der aktuelle authentifizierte User
     * @param SharedSave $sharedSave Die Einladung die abgelehnt oder angenommen werden soll
     * @return bool Ob der User die Einladung ablehnen oder annehmen darf
     */
    public function acceptDecline(User $user, SharedSave $sharedSave): bool
    {
        return $sharedSave->user_id === $user->id && !$sharedSave->revoked;
    }

    /**
     * Man darf keine Einladung löschen
     *
     * @param User $user Der aktuelle authentifizierte User
     * @param SharedSave $sharedSave Die Einladung
     * @return false
     */
    public function delete(User $user, SharedSave $sharedSave): bool
    {
        return false;
    }

    /**
     * Man darf keine Einladung wieder herstellen
     *
     * @param User $user Der aktuelle authentifizierte User
     * @param SharedSave $sharedSave Die Einladung
     * @return false
     */
    public function restore(User $user, SharedSave $sharedSave): bool
    {
        return false;
    }

    /**
     * Man darf keine Einladungen komplett löschen
     *
     * @param User $user Der aktuelle authentifizierte User
     * @param SharedSave $sharedSave Die Einladung
     * @return false
     */
    public function forceDelete(User $user, SharedSave $sharedSave): bool
    {
        return false;
    }
}

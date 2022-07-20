<?php

namespace App\Policies;

use App\Helper\PermissionHelper;
use App\Models\InvitationLink;
use App\Models\Save;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Validation\Rules\In;

class InvitationLinkPolicy
{
    use HandlesAuthorization;

    /**
     * Nur im Debug-Modus dürfen alle Einträge gelesen werden
     *
     * @param User $user Der aktuell authentifizierte User
     * @return boolean Ob der User alle InvitationLink Einträge lesen darf
     */
    public function viewAny(User $user): bool
    {
        return config('app.debug');
    }

    /**
     * Alle user dürfen einzelne Einladungs-Links anschauen
     *
     * @param User $user Der aktuell authentifizierte User
     * @param InvitationLink $invitationLink Der zu überprüfende Einladungs-Link
     * @return boolean Ob der User den angegebenen Einladungslink gelesen werden soll
     */
    public function view(User $user, InvitationLink $invitationLink): bool
    {
        $shared_save = $invitationLink->safe->sharedSaves()->where('user_id', $user->id)->first();
        return is_null($shared_save) || !$shared_save->revoked;
    }

    /**
     * Einladungs-Links dürfen nur erstellt werden, wenn der User Admin Rechte auf den Speicherstand hat und der User nicht anonym ist
     *
     * @param User $user Der aktuell authentifizierte User
     * @param Save $save Der Speicherstand für den der Einladungslink erstellt werden soll
     * @return boolean Ob der User einen Einladungs-Link erstellen darf
     */
    public function create(User $user, Save $save): bool
    {
        return !$user->anonymous && $save->hasAtLeasPermission($user, PermissionHelper::$PERMISSION_ADMIN);
    }

    /**
     * Einladungs-Links dürfen nur erstellt werden, wenn der User Admin Rechte auf den Speicherstand hat und der User nicht anonym ist
     *
     * @param User $user Der aktuell authentifizierte User
     * @param InvitationLink $invitationLink Der Einladungs-Link, welcher bearbeitet werden soll
     * @return boolean Ob der User einen Einladungs-Link erstellen darf
     */
    public function update(User $user, InvitationLink $invitationLink): bool
    {
        return !$user->anonymous && $invitationLink->safe->hasAtLeasPermission($user, PermissionHelper::$PERMISSION_ADMIN);
    }

    /**
     * Einladungs-Links dürfen nur erstellt werden, wenn der User Admin Rechte auf den Speicherstand hat und der User nicht anonym ist
     *
     * @param User $user Der aktuell authentifizierte User
     * @param InvitationLink $invitationLink Der Einladungslink, welcher gelöscht werden soll
     * @return boolean Ob der User den Einladungs-Link löschen darf
     */
    public function delete(User $user, InvitationLink $invitationLink): bool
    {
        return !$user->anonymous && $invitationLink->safe->hasAtLeasPermission($user, PermissionHelper::$PERMISSION_ADMIN);
    }
}

<?php


namespace App\Http\Resources;


use App\Helper\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * Klasse, welche eine InvitationLink instanz in ein Array umwandelt
 */
class InvitationLinkResource extends JsonResource
{
    /**
     * Felder der InvitationLink instanz ohne das Token und updated_at Feld
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "expiry_date" => $this->expiry_date,
            "permission" => $this->permission,
            "save_id" => $this->save_id,
            "created_at" => $this->created_at,
            "token" => $this->when($this->safe->hasAtLeasPermission(Auth::user(), PermissionHelper::$PERMISSION_ADMIN), $this->token)
        ];
    }
}

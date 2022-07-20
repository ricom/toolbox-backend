<?php

namespace App\Http\Controllers;

use App\Helper\PermissionHelper;
use App\Http\Resources\InvitationLinkResource;
use App\Models\InvitationLink;
use App\Models\Save;
use App\Policies\InvitationLinkPolicy;
use App\Policies\SavePolicy;
use App\Services\TokenService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Controller welche funktionen für die Einladungslink Routen implementiert
 */
class InvitationLinkController extends Controller
{

    /**
     * Zeigt alle Einladungslinks an
     * @return AnonymousResourceCollection Alle Links als Collection
     * @throws AuthorizationException Wenn der User keine Berechtigung zum Abrufen aller Einladungslinks hat
     * @see InvitationLinkPolicy
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize("viewAny", InvitationLink::class);

        return InvitationLinkResource::collection(InvitationLink::all());
    }

    /**
     * Erstellt eine neue InvitationLink Instanz
     * @param Request $request Die aktuelle Request instanz
     * @param TokenService $tokenService Dependency Injection
     * @return \Illuminate\Http\RedirectResponse Code 201 mit Location Header beim erfolgreichen Anlegen der Resource
     * @throws AuthorizationException Wenn der User keine Berechtigung hat für den Speicherstand ein Einladungslink zu erstellen
     * @see InvitationLink
     * @see InvitationLinkPolicy
     */
    public function store(Request $request, TokenService $tokenService): \Illuminate\Http\RedirectResponse
    {
        $validate = $request->validate([
            "expiry_date" => "nullable|date",
            "permission" => "required|numeric|min:0|max:1",
            "save_id" => "required|exists:saves,id"
        ]);
        $save = Save::findOrFail($validate["save_id"]);
        $this->authorize("create", [InvitationLink::class, $save]);


        $invitation_link = new InvitationLink($validate);
        $invitation_link->save_id = $validate["save_id"];
        $invitation_link->token = $tokenService->createToken();
        $invitation_link->save();
        return response()->redirectToRoute('invitation-link.show', ['invitation_link' => $invitation_link->token]);
    }

    /**
     * Zeigt die angeforderte InvitationLink Resource an
     *
     * @param InvitationLink $invitation_link Die angeforderte InvitationLink Resource
     * @return InvitationLinkResource Resource mit der angeforderten InvitationLink instanz
     * @throws AuthorizationException Wenn der User keine Berechtigungen hat diese InvitationLink instanz zu lesen
     * @see InvitationLink
     * @see InvitationLinkResource
     * @see InvitationLinkPolicy
     */
    public function show(InvitationLink $invitation_link): InvitationLinkResource
    {
        $this->authorize("view", $invitation_link);

        return new InvitationLinkResource($invitation_link);
    }

    /**
     * Aktualisiert die angegeben InvitationLink Resource.
     *
     * Es werden ausschließlich übergebene Attribute überschrieben
     * @param Request $request Die aktuelle Request instanz
     * @param InvitationLink $invitation_link Die ausgewählte InvitationLink Resource
     * @return Response Code 200, wenn die Aktualisierung durchgeführt wurde
     * @throws AuthorizationException Wenn der User keine Berechtigung zum Bearbeiten der Resource besitzt
     * @see InvitationLink
     * @see InvitationLinkPolicy
     */
    public function update(Request $request, InvitationLink $invitation_link): Response
    {
        $this->authorize("update", $invitation_link);

        $validate = $request->validate([
            "expiry_date" => "nullable|date",
            "permission" => "numeric|min:0|max:1"
        ]);

        $invitation_link->fill($validate);
        $invitation_link->save();

        return response()->noContent(Response::HTTP_OK);

    }

    /**
     * Gibt alle InvitationLink Resource zurück, welche zu der angegebenen Save Resource gehören
     * @param Save $save Die in der Url angegebene Save Resource
     * @return AnonymousResourceCollection Alle InvitationLinks als ResourceCollection
     * @throws AuthorizationException Wenn der User keine Berechtigung zum Ansehen der Save Resource hat
     * @see Save
     * @see SavePolicy
     * @see InvitationLinkResource
     */
    public function saveIndex(Save $save): AnonymousResourceCollection
    {

        $this->authorize("view", $save);


        return InvitationLinkResource::collection($save->invitationLinks()->paginate());
    }

    /**
     * Der aktuelle authentifizierte Nutzer nimmt die Einladung des Einladungslinks an
     * @param string $token der Token des InvitationLink
     * @param Request $request Die aktuelle Request instanz
     * @return Response Code 200, wenn die Einladung erfolgreich angenommen wurde, Code 403, wenn der Einladungslink abgelaufen ist
     * @throws ModelNotFoundException Wenn der token zu keiner InvitationLink Resource passt
     * @see InvitationLink
     */
    public function acceptInvite(string $token, Request $request): Response
    {

        $user = $request->user();
        $invitationLink = InvitationLink::whereToken($token)->firstOrFail();

        if (is_null($invitationLink->expiry_date) || Carbon::now() < $invitationLink->expiry_date) {

            $save = $invitationLink->safe;

            $existingContribution = $save->sharedSaves()->where('user_id', '=', $user->id)->first();

            if (is_null($existingContribution)) {
                $save->contributors()->attach($user, ["permission" => $invitationLink->permission]);
            } else {
                $this->authorize('acceptDecline', $existingContribution);

                $permission = $existingContribution->permission;

                $existingContribution->accept();
                // check if new permission is equal or higher than old permission
                if (PermissionHelper::isAtLeastPermission($permission, $invitationLink->permission)) {
                    $existingContribution->permission = $permission;
                    $existingContribution->save();
                }
            }

            return response()->noContent(Response::HTTP_OK);
        } else {
            return response()->noContent(Response::HTTP_FORBIDDEN);
        }

    }

    /**
     * Löscht den angegeben InvitationLink
     * @param InvitationLink $invitation_link Die in der URL angegebene InvitationLink Resource
     * @return Response Code 200, wenn der InvitationLink erfolgreich gelöscht wurde
     * @throws AuthorizationException Wenn der User keine Berechtigung hat den angegebenen InvitationLink zu löschen
     * @see InvitationLinkPolicy
     * @see InvitationLink
     */
    public function destroy(InvitationLink $invitation_link): Response
    {
        $this->authorize("delete", $invitation_link);
        $invitation_link->delete();
        return response()->noContent(Response::HTTP_OK);
    }
}

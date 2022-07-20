<?php

namespace App\Http\Controllers;

use App\Http\Resources\SharedSaveResource;
use App\Mail\SaveInvitationEmail;
use App\Models\Save;
use App\Models\SharedSave;
use App\Models\User;
use App\Policies\SharedSavePolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

/**
 * Controller, welcher Routen zum Verwalten von Freigegebenen Speicherständen implementiert
 * @package App\Http\Controllers
 */
class SharedSaveController extends Controller
{
    /**
     * Zeigt alle SharedSave Einträge an
     * @return AnonymousResourceCollection Alle SharedSave Einträge als ResourceCollection
     * @throws AuthorizationException Wenn der User keine Berechtigung besitzt alle SharedSave Einträge zu sehen
     * @see SharedSave
     * @see SharedSavePolicy::viewAny()
     * @see SharedSaveResource
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize("viewAny", SharedSave::class);

        return SharedSaveResource::collection(SharedSave::paginate());
    }


    /**
     * Zeigt alle SharedSave Einträge für den ausgewählten Speicherstand an
     * @param Save $save Der in der Url definierte Speicherstand
     * @return AnonymousResourceCollection Alle SharedSave Einträge, welche zu dem ausgewählten Speicherstand gehört, als Resource Collection
     * @throws AuthorizationException Wenn der User keine Berechtigung hat die SharedSave Einträge des Speicherstandes anzuschauen besitzt
     * @see SharedSave
     * @see SharedSavePolicy::viewOfSave()
     * @see SharedSaveResource
     */
    public function indexSave(Save $save): AnonymousResourceCollection
    {
        $this->authorize("viewOfSave", [SharedSave::class, $save]);
        return SharedSaveResource::collection($save->sharedSaves()->paginate());
    }


    /**
     * Zeigt alle SharedSave Einträge für den ausgewählten User an
     *
     * @param User $user der in der Url definierte User
     * @return AnonymousResourceCollection Die SharedSave Einträge, des definierten Users, als ResourceCollection
     * @throws AuthorizationException Wenn der User keine Berechtigung besitzt die SharedSave Einträge des Users anzusehen
     * @see SharedSave
     * @see SharedSavePolicy::viewOfUser()
     * @see SharedSaveResource
     */
    public function indexUser(User $user): AnonymousResourceCollection
    {
        $this->authorize("viewOfUser", [SharedSave::class, $user]);
        return SharedSaveResource::collection($user->sharedSaves()->paginate());
    }

    /**
     * Erstellt einen neuen SharedSave Eintrag
     *
     * @param Request $request Die aktuelle Request instanz
     * @param Save $save Der in der Url definierte Speicherstand
     * @param User $user Der in der Url definierte User
     * @return Response Code 201, wenn die SharedSave Resource erstellt wurde
     * @throws AuthorizationException Wenn der User keine Berechtigung besitzt um eine neue SharedSave Resource zu erstellen
     * @see SharedSave
     * @see SharedSavePolicy::create()
     * @see SharedSaveResource
     */
    public function store(Request $request, Save $save, User $user): Response
    {
        $validated = $request->validate([
            "permission" => ["required", "integer", "min:0", "max:2"]
        ]);
        $this->authorize("create", [SharedSave::class, $save]);
        $shared_save = new SharedSave($validated);
        $shared_save->user_id = $user->id;
        $shared_save->accepted = false;
        $save->invitations()->save($shared_save);

        Mail::to($user->email)->send(new SaveInvitationEmail($user->username, $save->name, $shared_save->id));

        return response()->created("contribution", $shared_save);
    }

    /**
     * Zeigt den definierten SharedSave Eintrag
     * @param SharedSave $sharedSave Der in der Url definierten SharedSave Eintrag
     * @return SharedSaveResource Der ausgewählte SharedSave Eintrag in einer SharedSaveResource
     * @throws AuthorizationException Wenn der User keine Berechtigung besitzt, um den SharedSave Eintrag anzusehen
     * @see SharedSave
     * @see SharedSavePolicy::view()
     * @see SharedSaveResource
     */
    public function show(SharedSave $sharedSave): SharedSaveResource
    {
        $this->authorize("view", $sharedSave);

        return new SharedSaveResource($sharedSave);
    }

    /**
     * Aktualisiert den ausgewählten SharedSave Eintrag mit den übergebenen Attributen
     * @param Request $request Die aktuelle Request Instanz
     * @param SharedSave $sharedSave Der in der Url definierte SharedSave Eintrag
     * @return Response Code 200, wenn die Änderungen durchgeführt wurden
     * @throws AuthorizationException Wenn er User keine Berechtigung besitzt, um diesen SharedSave Eintrag zu bearbeiten
     */
    public function update(Request $request, SharedSave $sharedSave): Response
    {
        $this->authorize("update", $sharedSave);
        $validated = $request->validate([
            "permission" => ["integer", "min:0", "min:2"],
            "revoked" => ["boolean"],
        ]);
        $sharedSave->fill($validated);
        $sharedSave->save();
        return \response()->noContent(Response::HTTP_OK);
    }


    /**
     * Akzeptiert die zuvor erstellte Einladung an dem definierten Speicherstand mitzuwirken
     * @param Request $request Die aktuelle Request instanz
     * @param SharedSave $sharedSave Der in der Url definierte SharedSave Eintrag
     * @return Response Code 200, wenn das Akzeptieren erfolgreich durchgeführt wurde. Code 409, wenn die Einladung bereits deaktiviert wurde
     * @throws AuthorizationException Wenn der User keine Berechtigung besitzt diese Einladung anzunehmen
     * @see SharedSave
     * @see SharedSavePolicy::acceptDecline()
     * @see SharedSaveResource
     */
    public function accept(Request $request, SharedSave $sharedSave)
    {
        $this->authorize("acceptDecline", $sharedSave);
        if (!$sharedSave->revoked) {
            $sharedSave->accept();
            $sharedSave->save();
            return \response()->noContent(Response::HTTP_OK);
        } else {
            return \response(null, Response::HTTP_CONFLICT);
        }
    }

    /**
     * Lehnt die zuvor erstellte Einladung an dem definierten Speicherstand mitzuwirken ab
     * @param Request $request Die aktuelle Request instanz
     * @param SharedSave $sharedSave Der in der Url definierte SharedSave Eintrag
     * @return Response Code 200, wenn das Ablehnen erfolgreich durchgeführt wurde
     * @throws AuthorizationException Wenn der User keine Berechtigung besitzt diese Einladung abzulehnen
     * @see SharedSave
     * @see SharedSavePolicy::acceptDecline()
     * @see SharedSaveResource
     */
    public function decline(Request $request, SharedSave $sharedSave)
    {
        $this->authorize("acceptDecline", $sharedSave);
        $sharedSave->decline();
        $sharedSave->save();
        return \response(null, Response::HTTP_OK);
    }

    /**
     * Löscht den definierten SharedSave Eintrag
     * @param SharedSave $sharedSave Der in der Url definierte SharedSave Eintrag
     * @return Response Code 200, wenn der Eintrag gelöscht wurde
     * @throws AuthorizationException Wenn der User keine Berechtigung besitzt den SharedSave Eintrag zu löschen
     * @see SharedSave
     * @see SharedSavePolicy::delete()
     * @see SharedSaveResource
     */
    public function destroy(SharedSave $sharedSave)
    {
        $this->authorize("delete", $sharedSave);
        $sharedSave->delete();
        return response(null, Response::HTTP_OK);
    }
}

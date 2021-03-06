<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaveResource;
use App\Http\Resources\SimpleSaveResource;
use App\Models\Save;
use App\Policies\SavePolicy;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

/**
 * Controller, welcher Routen zum Verwalten von Speicherständen implementiert
 * @package App\Http\Controllers
 */
class SaveController extends Controller
{
    /**
     * Zeigt alle Speicherstände an
     * @return AnonymousResourceCollection Speicherstände als ResourceCollection
     * @throws AuthorizationException Wenn der User keine Berechtigung zum Ansehen aller Speicherstände hat
     * @see Save
     * @see SavePolicy
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize("viewAny", Save::class);

        return SimpleSaveResource::collection(Save::with("contributors")->paginate());
    }

    /** Erstellt einen neuen Speicherstand
     * @param Request $request Die aktuelle Request instanz
     * @return JsonResponse Code 201, wenn der Speicherstand erfolgreich erstellt wurde
     * @throws AuthorizationException Wenn der User keine Berechtigung zum Erstellen von Speicherständen hat
     * @see Save
     * @see SavePolicy
     */
    public function store(Request $request): JsonResponse
    {

        $this->authorize("create", Save::class);

        $validate = $request->validate([
            "name" => "required|string",
            "description"=>"string",
            "data" => "nullable|json",
            "tool_id" => "required|exists:tools,id"
        ]);

        $s = new Save($validate);
        $s->tool_id = $validate["tool_id"];
        $s->owner_id = $request->user()->id;
        $s->save();
//        return response()->created('saves', $s);
        return response()->json(new SaveResource($s), 201);
    }

    /**
     * Gibt den ausgewählten Speicherstand zurück
     *
     * Das <code>last_opened</code> Attribut wird auf die aktuelle Zeit gesetzt.
     *
     * @param Save $save Der in der Url definierte Speicherstand
     * @return SaveResource Die Resource des in der Url definierten Speicherstandes
     * @throws AuthorizationException Wenn der User keine Berechtigung zum Anschauen des Speicherstandes hat
     * @see Save
     * @see SavePolicy
     * @see Save::$last_opened
     */
    public function show(Save $save): SaveResource
    {
        $this->authorize("view", $save);
        $save->last_opened = Carbon::now();
        $save->save();
        return new SaveResource($save);
    }

    /** Aktualisiert den ausgewählten Speicherstand mit den übergebenen Daten
     *
     *  Response-Codes:
     *  - 200: Änderungen übernomen
     *  - 424: Speicherstand muss vorher gesperrt werden
     *  - 423: Speicherstand ist gerade von einem anderen User gesperrt
     *
     * @param Request $request Die aktuelle Request instanz
     * @param Save $save Der in der Url definierte Speicherstand
     * @return Response Gibt einen passenden Response-Code zurück
     * @throws AuthorizationException Wenn der User keine Berechtigung hat den Speicherstand zu überschreiben
     * @see Save
     * @see SavePolicy
     */
    public function update(Request $request, Save $save): Response
    {
        $this->authorize("update", $save);
        $user = $request->user();

        if ($request->has("lock")) {

            $validated = $request->validate([
                "lock" => "required|boolean",
                "data" => "prohibited",
                "name" => "prohibited",
                "description"=>"prohibited"
            ]);

            if (is_null($save->locked_by_id) || $save->owner_id === $user->id) {
                if ($validated["lock"]) {
                    $save->locked_by_id = $user->id;
                    $save->last_locked = Carbon::now();
                } else {
                    $save->locked_by_id = null;
                }

                $save->save();
                return response()->noContent(Response::HTTP_OK);
            } else {
                return response(["message" => "The save needs to get locked in advance"], Response::HTTP_FAILED_DEPENDENCY);
            }
        } else {
            $validated = $request->validate([
                "data" => "nullable|json",
                "name" => "string",
                "description" => "string",
                "lock" => "prohibited"
            ]);

            if ($save->locked_by_id === $user->id) {
                $save->fill($validated);
                $save->save();
                return response()->noContent(Response::HTTP_OK);
            } else {
                return response()->noContent(Response::HTTP_LOCKED);
            }
        }
    }

    /**
     * Löschten den ausgewählten Speicherstand
     * @param Save $save Den Speicherstand, welcher in der Url definiert wurde
     * @return Response code 200, wenn der Speicherstand gelöscht wurde
     * @throws AuthorizationException Wenn der User keine Berechtigung besitzt den Speicherstand zu löschen
     */
    public function destroy(Save $save): Response
    {
        $this->authorize("delete", $save);
        $save->delete();
        return response()->noContent(Response::HTTP_OK);
    }
}

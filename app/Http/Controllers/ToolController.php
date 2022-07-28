<?php

namespace App\Http\Controllers;

use App\Http\Resources\ToolResource;
use App\Models\Tool;
use App\Policies\ToolPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Controller, welcher Routen zum Verwalten von Routen implementiert
 * @package App\Http\Controllers
 */
class ToolController extends Controller
{
    /**
     * Zeigt alle Tools an
     * @return AnonymousResourceCollection Alle Tools als ResourceCollection
     * @throws AuthorizationException Wenn er User keine Berechtigung hat alle Tools anzusehen
     * @see Tool
     * @see ToolPolicy::viewAny()
     * @see ToolResource
     */
    public function index()
    {
        $this->authorize("viewAny", Tool::class);

        return ToolResource::collection(Tool::paginate());
    }

    /**
     * Erstellt ein neues Tool
     * @param Request $request Die aktuelle Request Instanz
     * @return Response Code 201, wenn das Tool erstellt wurde
     * @throws AuthorizationException Wenn der User keine Berechtigung besitzt ein Tool zu Erstellen
     * @see Tool
     * @see ToolPolicy::create()
     * @see ToolResource
     */
    public function store(Request $request): Response
    {
        $this->authorize("create", Tool::class);

        $tool = new Tool($request->input());
        $tool->save();

        return response()->created('tools', new ToolResource($tool));
    }

    /** Zeigt ein ausgewähltes Tool an
     * @param Tool $tool Das in der Url definierte Tool
     * @return ToolResource Das ausgewählte Tool als ToolResource
     * @throws AuthorizationException Wenn der User keine Berechtigung besitzt dieses Tool anzuschauen
     * @see Tool
     * @see ToolPolicy::view()
     * @see ToolResource
     */
    public function show(Tool $tool): ToolResource
    {
        $this->authorize("view", $tool);

        return new ToolResource($tool);
    }

    /**
     * Aktualisiert das ausgewählte Toll mit den angegebenen Attributen
     * @param Request $request Die aktuelle Request Instanz
     * @param Tool $tool Das in der Url definierte Tool
     * @return Response Code 200, wenn das Tool erfolgreich geändert wurde
     * @throws AuthorizationException Wenn der User keine Berechtigung zum Ändern besitzt
     * @see Tool
     * @see ToolPolicy::update()
     * @see ToolResource
     */
    public function update(Request $request, Tool $tool)
    {
        $this->authorize("update", $tool);
        $tool->fill($request->all());
        $tool->save();
        return response()->noContent(Response::HTTP_OK);
    }

    /**
     * Löscht das ausgewählte Tool
     * @param Tool $tool Das in der Url definierte Tool
     * @return Response Code 200, wenn das Löschen erfolgreich war
     * @throws AuthorizationException Wenn der User keine Berechtigung besitzt, um das Tool zu löschen
     */
    public function destroy(Tool $tool)
    {
        $this->authorize("destroy", $tool);
        $tool->delete();
        return response()->noContent(Response::HTTP_OK);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Resources\UserSearchResultResource;
use App\Mail\AccountDeleteEmail;
use App\Models\EmailVerification;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Rules\EmailBlockList;
use App\Services\EmailService;
use App\Services\UserService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Validator;

/**
 * Controller, welcher Routen zum Verwalten von Usern implementiert
 * @package App\Http\Controllers
 */
class UserController extends Controller
{

    /**
     * Das regex, welches Benutzt wird um sicherzustellen, dass das User Password valide ist
     * @var string
     */
    public static $passwordRegex = "/^(?=.*[a-zäöüß])(?=.*[A-ZÄÖÜ])(?=.*\d)(?=.*[$&§+,:;=?@#|'<>.^*()%!_-])[A-Za-zäöüßÄÖÜ\d$&§+,:;=?@#|'<>.^*()%!_-].+$/";


    /**
     * Zeigt alle User an
     * @return AnonymousResourceCollection Alle User als ResourceCollection
     * @throws AuthorizationException Wenn der User keine Berechtigung zum Ansehen aller User besitzt
     * @see User
     * @see UserPolicy::viewAny()
     * @see UserResource
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize("viewAny", User::class);
        return UserResource::collection(User::with(['saves', 'invitedSaves', 'accessibleShares'])->whereNotNull("email_verified_at")->paginate());
    }

    /**
     * Erstellt einen neuen User
     *
     * @param Request $request Die aktuelle Request instanz
     * @param EmailService $emailService Dependency Injection
     * @param UserService $userService Dependency Injection
     * @return Response Code 201, wenn ein User erstellt wurde
     * @throws ValidationException Wenn die Eingabedaten nicht valide sind
     */
    public function store(Request $request, EmailService $emailService, UserService $userService): Response
    {
        $validated = Validator::validate($request->all(), [
            "username" => ["required", "string", "unique:users"],
            "password" => ["required", "string", "min:8", "max:120", "regex:" . UserController::$passwordRegex],
            "email" => ["required", "email", new EmailBlockList($emailService), "unique:users,email", "unique:" . EmailVerification::class . ",email"],
            "anonymous_id" => ["integer", "exists:users,id"],
        ], [
            "password.regex" => __("passwords.invalid_regex")
        ]);


        if (array_key_exists("anonymous_id", $validated)) {
            $u = User::find($validated["anonymous_id"]);
            if ($u->anonymous) {
                $userService->upgradeAnonymousUser($u, $validated, $emailService);
            }

        } else {
            $u = new User();
            $userService->updateUser($u, array_merge(["anonymous" => false,], $validated), $emailService);
        }

        return \response()->created('users', $u);
    }


    /**
     * Erstellt einen Anonymen User.
     *
     * Username und Password des neuen Users werden im Body zurückgegeben
     *
     * @return Response Code 201, wenn das erstellten erfolgreich war. Response enthält username und password im Body
     * @throws Exception Wenn es ein Problem beim Erstellen des Users gab
     */
    public function storeAnonymous(UserService $userService): Response
    {
        $password = md5(microtime());
        $u = $userService->createAnonymousUser($password);
        $u->save();

        return response()->created('users', $u)->setContent(["username" => $u->username, "password" => $password]);
    }

    /**
     * Zeigt einen ausgewählten User an
     * @param User $user Der in der Url definierte User
     * @return UserResource Der ausgewählte User als UserResource
     * @throws AuthorizationException Wenn der User keine Berechtigung den ausgewählten User anzusehen
     * @see User
     * @see UserPolicy::view()
     * @see UserResource
     */
    public function show(User $user): UserResource
    {
        $this->authorize("view", $user);

        return new UserResource($user);
    }

    /**
     * Ändert eines ausgewählten Nutzers zu den übergebenen Attributen
     * @param Request $request Die aktuelle Request Instanz
     * @param User $user Der in der Url definierte User
     * @param EmailService $emailService Dependency Injection
     * @param UserService $userService Dependency Injection
     * @return JsonResponse|Response
     * @throws AuthorizationException Wenn der aktuelle User keine Berechtigung hat den ausgewählten User zu verändern
     * @throws ValidationException Wenn die Eingabeparameter nicht valide sind
     * @see User
     * @see UserPolicy::update()
     * @see UserResource
     */
    public function update(Request $request, User $user, EmailService $emailService, UserService $userService)
    {
        $this->authorize("update", $user);

        $validated = Validator::validate($request->all(), [
            "username" => ["string", "unique:users"],
            "password" => ["string", "min:8", "max:120", "regex:" . UserController::$passwordRegex],
            "current_password" => ["required", "string"]
            // "email" => ["email", "unique:users,email", new EmailBlockList($emailService), "unique:" . EmailVerification::class . ",email"]
        ], [
            "password.regex" => __("passwords.invalid_regex")
        ]);

        if (!Hash::check($validated["current_password"], $user->password)) {
            return \response()->json(["msg" => "current_password is wrong"], 401);
        }

        $userService->updateUser($user, $validated, $emailService);
        return response()->noContent(Response::HTTP_OK);
    }

    /** Löscht den ausgewählten User
     * @param User $user Den in der Url definierten User
     * @return Response Code 200, wenn das Löschen erfolgreich war
     * @throws AuthorizationException Wenn der User keine Berechtigung besitzt den
     */
    public function destroy(User $user): Response
    {
        $this->authorize("delete", $user);
        // Info: for development this should probably be disabled, since no email can be sent, at least this was throwing errors because of that
        Mail::to($user->email)->send(new AccountDeleteEmail($user->username));
        $user->delete();
        return response()->noContent(Response::HTTP_OK);
    }

    /**
     * Gibt zurück ob der angefragte Username bereits benutzt wird
     * @param Request $request Die aktuelle Request instanz
     * @param UserService $userService Dependency Injection
     * @return JsonResponse Body enthält available attribut, welches angibt ob der Username bereits benutzt wird
     */
    public function checkUsername(Request $request, UserService $userService): JsonResponse
    {
        $validated = $request->validate([
            "username" => ["string", "required"]
        ]);
        $available = $userService->checkUsername($validated["username"]);
        return response()->json(["data" => [
            "available" => $available,
            "reason" => $available ? "" : "taken"
        ]]);

    }

    /**
     * Gibt zurück, ob die angefragte E-Mail bereits benutzt wird
     *
     * Es werden auch die noch nicht bestätigten E-Mail-Adressen berücksichtigt
     * @param Request $request Die aktuelle Request instanz
     * @param UserService $userService Dependency Injection
     * @return JsonResponse Body enthält available attribut, welches angibt, ob die E-Mail bereits benutzt wird
     */
    public function checkEmail(Request $request, UserService $userService, EmailService $emailService): JsonResponse
    {
        $validated = $request->validate([
            "email" => ["string", "required"]
        ]);
        $reason = "";

        $validEmail = filter_var($validated["email"], FILTER_VALIDATE_EMAIL);
        if ($validEmail !== false) {
            $allowed = $emailService->checkBlockLists($validated["email"]);
            $available = false;
            if ($allowed) {
                $available = $userService->checkEmail($validated["email"]);
                if (!$available) {
                    $reason = "taken";
                }
            } else {
                $reason = "blocked";
            }
        } else {
            $available = false;
            $reason = "invalid";
        }


        return response()->json(["data" => [
            "available" => $available,
            "reason" => $reason,
        ]]);

    }

    /**
     * gibt User Vorschläge für einen Suchstring zurück.
     *
     * Durchsucht zuerst die E-Mail spalte und wenn eine genaue übereinstimmung gibt, wird diese zurückgegeben.
     * Anschließend wird die Username nach dem Suchstring als substring des usernamen durchsucht.
     * @param Request $request
     * @return AnonymousResourceCollection eine limitierte Anzahl an User Vorschlägen
     */
    public function searchUser(Request $request): AnonymousResourceCollection
    {


        $validated = $request->validate([
            "name" => ["required", "string"]
        ]);


        $name = $validated["name"];

        $this->authorize('searchAny', [User::class, $name]);

        $mailUsers = User::whereEmail($name)->limit(1)->get();

        if ($mailUsers->count() > 0) {
            return UserSearchResultResource::collection($mailUsers);
        } else if ($request->user()->anonymous === true) {
            return UserSearchResultResource::collection([]);
        }

        $usernameUsers = User::where('username', 'LIKE', '%' . $name . '%')->limit(5)->get();

        return UserSearchResultResource::collection($usernameUsers);
    }
}

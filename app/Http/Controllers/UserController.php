<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Mail\AccountDeleteEmail;
use App\Models\EmailVerification;
use App\Models\User;
use App\OpenApi\Parameters\EmailAvailabilityParameters;
use App\OpenApi\Parameters\LimitableParameters;
use App\OpenApi\Parameters\UsernameAvailabilityParameters;
use App\OpenApi\RequestBodies\StoreUserRequestBody;
use App\OpenApi\RequestBodies\UpdateUserRequestBody;
use App\OpenApi\Responses\AnonymousCreatedResponse;
use App\OpenApi\Responses\AvailabilityResponse;
use App\OpenApi\Responses\OkResponse;
use App\OpenApi\Responses\UserCreatedResponse;
use App\OpenApi\Responses\NotFoundResponse;
use App\OpenApi\Responses\UnauthenticatedResponse;
use App\OpenApi\Responses\UnauthorizedResponse;
use App\OpenApi\Responses\UserListResponse;
use App\OpenApi\Responses\UserResponse;
use App\OpenApi\Responses\ValidationFailedResponse;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Validator;
use Vyuldashev\LaravelOpenApi\Attributes\Operation;
use Vyuldashev\LaravelOpenApi\Attributes\Parameters;
use Vyuldashev\LaravelOpenApi\Attributes\PathItem;
use Vyuldashev\LaravelOpenApi\Attributes\RequestBody;

/**
 * Controller, welcher Routen zum Verwalten von Usern implementiert
 * @package App\Http\Controllers
 *
 */
#[PathItem]
class UserController extends Controller
{

    /**
     * Das regex, welches Benutzt wird um sicherzustellen, dass das User Password valide ist
     * @var string
     */
    public static $passwordRegex = "/^(?=.*[a-zäöüß])(?=.*[A-ZÄÖÜ])(?=.*\d)(?=.*[$&§+,:;=?@#|'<>.^*()%!_-])[A-Za-zäöüßÄÖÜ\d$&§+,:;=?@#|'<>.^*()%!_-].+$/";


    /**
     * Zeigt alle User an
     *
     * @return AnonymousResourceCollection Alle User als ResourceCollection
     * @throws AuthorizationException Wenn der User keine Berechtigung zum Ansehen aller User besitzt
     * @see User
     * @see UserPolicy::viewAny()
     * @see UserResource
     */
    #[Operation(tags: ['users'])]
    #[Parameters(LimitableParameters::class)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UserListResponse::class, statusCode: 200)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UnauthenticatedResponse::class, statusCode: 401)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UnauthorizedResponse::class, statusCode: 403)]
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
    #[Operation(tags: ['users'], security: '')]
    #[RequestBody(StoreUserRequestBody::class)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UserCreatedResponse::class, statusCode: 201)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UnauthenticatedResponse::class, statusCode: 401)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UnauthorizedResponse::class, statusCode: 403)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(ValidationFailedResponse::class, statusCode: 422)]
    public function store(Request $request, EmailService $emailService, UserService $userService): Response
    {
        $validated = Validator::validate($request->all(), [
            "username" => ["required", "string", "unique:users"],
            "password" => ["required", "string", "min:8", "max:120", "regex:" . UserController::$passwordRegex],
            "email" => ["required", "email", new EmailBlockList($emailService), "unique:users,email", "unique:" . EmailVerification::class . ",email"],
            "anonymous_id" => ["integer", "exists:users,id"],
            "anonymous_password" => ["required_with:anonymous_id", "string"],
        ], [
            "password.regex" => __("passwords.invalid_regex")
        ]);


        if (array_key_exists("anonymous_id", $validated)) {
            $u = User::find($validated["anonymous_id"]);
            if ($u->anonymous && Hash::check($validated["anonymous_password"], $u->password)) {
                $userService->upgradeAnonymousUser($u, $validated, $emailService);
                Auth::login($u);
            }

        } else {
            $u = new User();
            $userService->updateUser($u, array_merge(["anonymous" => false,], $validated), $emailService);
            Auth::login($u);
        }

        if (is_null($u)) {
            return \response(null, 500);
        }


        return \response()->created('users', new UserResource($u));
    }


    /**
     * Erstellt einen Anonymen User.
     *
     * Username und Password des neuen Users werden im Body zurückgegeben
     *
     * @return Response Code 201, wenn das erstellten erfolgreich war. Response enthält username und password im Body
     * @throws Exception Wenn es ein Problem beim Erstellen des Users gab
     */
    #[Operation(tags: ['users'], security: '')]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(AnonymousCreatedResponse::class, statusCode: 201)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UnauthenticatedResponse::class, statusCode: 401)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UnauthorizedResponse::class, statusCode: 403)]
    public function storeAnonymous(UserService $userService): Response
    {
        $password = md5(microtime());
        $u = $userService->createAnonymousUser($password);
        $u->save();
        Auth::login($u);

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
    #[Operation(id: "showUser", tags: ['users'])]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UserResponse::class, statusCode: 200)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UnauthenticatedResponse::class, statusCode: 401)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UnauthorizedResponse::class, statusCode: 403)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(NotFoundResponse::class, statusCode: 404)]
    public function show(User $user): UserResource
    {
        $this->authorize("view", $user);

        return new UserResource($user);
    }

    /**
     * Ändert die Attribute eines ausgewählten Nutzers zu den übergebenen
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
    #[Operation(id: "test", tags: ['users'])]
    #[RequestBody(UpdateUserRequestBody::class)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UnauthenticatedResponse::class, statusCode: 401)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UnauthorizedResponse::class, statusCode: 403)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(NotFoundResponse::class, statusCode: 404)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(ValidationFailedResponse::class, statusCode: 422)]
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
            return \response()->json(["message" => "current_password is wrong"], 401);
        }

        $userService->updateUser($user, $validated, $emailService);
        return response()->noContent(Response::HTTP_OK);
    }

    /** Löscht den ausgewählten User
     * @param User $user Den in der Url definierten User
     * @return Response Code 200, wenn das Löschen erfolgreich war
     * @throws AuthorizationException Wenn der User keine Berechtigung besitzt den
     */
    #[Operation(tags: ['users'])]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(OkResponse::class, statusCode: 200)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UnauthenticatedResponse::class, statusCode: 401)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(UnauthorizedResponse::class, statusCode: 403)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(NotFoundResponse::class, statusCode: 404)]
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
    #[Operation(tags: ['users'], security: '')]
    #[Parameters(UsernameAvailabilityParameters::class)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(AvailabilityResponse::class, statusCode: 200)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(ValidationFailedResponse::class, statusCode: 422)]
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
    #[Operation(tags: ['users'], security: '')]
    #[Parameters(EmailAvailabilityParameters::class)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(AvailabilityResponse::class, statusCode: 200)]
    #[\Vyuldashev\LaravelOpenApi\Attributes\Response(ValidationFailedResponse::class, statusCode: 422)]
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
}

<?php

use App\Http\Controllers\EmailController;
use App\Http\Controllers\InvitationLinkController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\SaveController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SharedSaveController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//User
Route::apiResource('users', UserController::class)->only('store');
Route::post('users/anonymous', [UserController::class, "storeAnonymous"]);

Route::get("checkUsername", [UserController::class, "checkUsername"]);
Route::get("checkEmail", [UserController::class, "checkEmail"]);

//Email
Route::put('email/verify/{token}', [EmailController::class, 'verify']);

// PasswordReset
Route::get('password/{token}', [PasswordController::class, 'show']);
Route::post('password-reset', [PasswordController::class, 'forgotPassword']);
Route::put('update-password/{token}', [PasswordController::class, 'updatePassword']);

Route::group(["middleware" => ["auth:api", "activityLog"]], function () {
    Route::apiResources([
        "tools" => ToolController::class,
        "saves" => SaveController::class,
        "invitation-link" => InvitationLinkController::class,
    ]);

    Route::apiResource('settings', SettingController::class)->only(["index","show"]);

    Route::apiResource('users.settings', UserSettingController::class);


    Route::put("/contribution/{sharedSave}/accept", [SharedSaveController::class, "accept"]);
    Route::put("/contribution/{sharedSave}/decline", [SharedSaveController::class, "decline"]);
    // contributors
    Route::apiResource("contribution", SharedSaveController::class, [
        "except" => ["store"]
    ])->parameter("contribution", "sharedSave");


    Route::get("/saves/{save}/contributors", [SharedSaveController::class, "indexSave"])->name("contributions.index.save");
    Route::get("/users/{user}/contributions", [SharedSaveController::class, "indexUser"])->name("contributions.index.user");
    Route::post("/saves/{save}/contributors/{user}", [SharedSaveController::class, "store"])->name("contribution.store.save");


    // Users
    Route::get('users/{user}/saves', [\App\Http\Controllers\UserSavesController::class,'index']);

    Route::apiResource('users', UserController::class)->except('store');

    // InvitationLink
    Route::get('saves/{save}/invitation-links', [InvitationLinkController::class, "saveIndex"]);
    Route::get('invitation-link/{token}/accept', 'App\Http\Controllers\InvitationLinkController@acceptInvite');


});

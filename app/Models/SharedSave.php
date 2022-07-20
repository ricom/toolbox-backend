<?php

namespace App\Models;

use App\Traits\Limitable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * Verknüpft Nutzer und Speicherstände
 *
 * @property int $id Id des SharedSaves Eintrags
 * @property int $user_id id des Users
 * @property int $save_id id des Speicherstandes
 * @property int $permission Berechtigung die dem User zugeschrieben wird
 * @property int $accepted Ob der User die Einladung akzeptiert hat
 * @property int $revoked Ob der einladende User die Einladung zurückgenommen hat
 * @property int $declined Ob der User die Einladung abgelehnt hat
 * @property Carbon|null $created_at Timestamp an dem der Eintrag erstellt wurde
 * @property Carbon|null $updated_at Timestamp an dem der Eintrag das letzte Mal geändert wurde
 * @property-read Save $safe Speicherstand
 * @property-read User $user User
 * @method static Builder|SharedSave whereAccepted($value)
 * @method static Builder|SharedSave whereCreatedAt($value)
 * @method static Builder|SharedSave whereId($value)
 * @method static Builder|SharedSave wherePermission($value)
 * @method static Builder|SharedSave whereSaveId($value)
 * @method static Builder|SharedSave whereUpdatedAt($value)
 * @method static Builder|SharedSave whereUserId($value)
 * @method static Builder|SharedSave whereDeclined($value)
 * @method static Builder|SharedSave whereRevoked($value)
 * @method static Builder|SharedSave newModelQuery()
 * @method static Builder|SharedSave newQuery()
 * @method static Builder|SharedSave query()
 * @mixin Eloquent
 */
class SharedSave extends Pivot
{
    use Limitable;

    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'permission',
        'revoked'
    ];

    /**
     * Beschreibt die Beziehung zu users Tabelle
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * renamed because of function overloading
     * @return BelongsTo
     */
    public function safe(): BelongsTo
    {
        return $this->belongsTo(Save::class, "save_id");
    }

    public function accept()
    {
        $this->accepted = true;
        $this->declined = false;
    }

    public function decline()
    {
        $this->accepted = false;
        $this->declined = true;
    }

}

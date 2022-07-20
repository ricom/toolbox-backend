<?php

namespace App\Models;

use App\Traits\Limitable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\InvitationLink
 *
 * @property Carbon|null $expiry_date Zeitpunkt an dem der Link abläuft
 * @property int $permission Rechte die ein User kriegt, wenn er mit dem Link auf den Speicherstand zugreift
 * @property int $save_id id des zugehörigen Speicherstands
 * @property string $token Token des Einladungslinks
 * @property Carbon|null $created_at Timestamp des Zeitpunktes der Erstellung
 * @property Carbon|null $updated_at Timestamp des Zeitpunktes der letzten Änderung
 * @property-read Save $safe zugehöriger Speicherstand
 * @method static Builder|InvitationLink newModelQuery()
 * @method static Builder|InvitationLink newQuery()
 * @method static Builder|InvitationLink query()
 * @method static Builder|InvitationLink whereCreatedAt($value)
 * @method static Builder|InvitationLink whereExpiryDate($value)
 * @method static Builder|InvitationLink whereId($value)
 * @method static Builder|InvitationLink wherePermission($value)
 * @method static Builder|InvitationLink whereSaveId($value)
 * @method static Builder|InvitationLink whereUpdatedAt($value)
 * @method static Builder|InvitationLink whereToken($value)
 * @mixin Eloquent
 */
class InvitationLink extends Model
{
    use HasFactory, Limitable;


    protected $primaryKey = "token";

    protected $keyType = "string";

    public $incrementing = false;

    /**
     * Attribute, welche Massen zuweisbar sind
     *
     * @var array
     */
    protected $fillable = [
        'permission',
        'expiry_date',
    ];

    /**
     * Zugehörigkeit, welche Attribute zu welchen nativen Typen gecastet werden soll.
     *
     * @var array
     */
    protected $casts = [
        'expiry_date' => 'datetime',
    ];

    /**
     * Beschreibt die Beziehung zur save Tabelle
     *
     * safe mit f geschrieben, weil eine save methode bereits existiert
     * @return BelongsTo
     */
    public function safe(): BelongsTo
    {
        return $this->belongsTo(Save::class, "save_id");
    }
}

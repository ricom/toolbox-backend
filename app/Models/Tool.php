<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Ein Tool wie "SWOT-Analyse", welches im Frontend implementiert ist
 *
 * @property int $id id des Tools
 * @property string $name Name des Tools
 * @property Carbon|null $created_at Zeitpunkt an dem das Tool erstellt wurde
 * @property Carbon|null $updated_at Zeitpunkt an dem das Tool das letzte Mal geändert wurde.
 * @method static Builder|Tool newModelQuery()
 * @method static Builder|Tool newQuery()
 * @method static Builder|Tool query()
 * @method static Builder|Tool whereCreatedAt($value)
 * @method static Builder|Tool whereId($value)
 * @method static Builder|Tool whereName($value)
 * @method static Builder|Tool whereUpdatedAt($value)
 * @property-read Collection|Save[] $saves Alle Speicherstände, die zu diesem Tool gehören
 * @mixin Eloquent
 * @property-read int|null $saves_count
 */
class Tool extends Model
{
    use HasFactory;

    /**
     * Attribute, welche Massen zuweisbar sind
     *
     * @var array
     */
    protected $fillable = [
        "name"
    ];


    /**
     * Beschreibt die Beziehung zu der saves Tabelle
     * @return HasMany
     */
    public function saves(): HasMany
    {
        return $this->hasMany(Save::class);
    }
}

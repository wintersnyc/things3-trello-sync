<?php

namespace App\Models\Concerns;

use App\Models\Heading;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read ?Heading $heading
 */
trait HasHeading
{
    public function headingRel(): BelongsTo
    {
        return $this->belongsTo(Heading::class, 'heading', 'uuid');
    }
}

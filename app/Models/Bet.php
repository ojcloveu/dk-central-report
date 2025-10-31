<?php

namespace App\Models;

use App\Traits\Utils\ResourceNumberFormat;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bet extends Model
{
    use CrudTrait;
    use ResourceNumberFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account',
        'channel',
        'trandate',
        'master',
        'min',
        'max',
        'count',
        'turnover',
        'winlose',
        'lp',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'trandate' => 'date',
        'min' => 'integer',
        'max' => 'integer',
        'count' => 'integer',
        'turnover' => 'decimal:2',
        'winlose' => 'decimal:2',
        'lp' => 'decimal:2',
        'bet_amount' => 'decimal:2',
    ];

    protected function lpColor(): Attribute
    {
        return Attribute::make(
            get: function () {
                $lp = $this->lp ?? $this->total_lp;
                $lpJson = config('settings.lp_color') ?? [];
                $lps = json_decode($lpJson, true);

                $defaultColor = 'gray';

                if (empty($lps) || !is_array($lps)) {
                    return $defaultColor;
                }

                foreach ($lps as $range) {
                    $from = (float) ($range['from'] ?? 0);
                    $to = (float) ($range['to'] ?? 0);
                    $color = $range['color'] ?? $defaultColor;

                    if ($lp >= $from && $lp <= $to) {
                        return $color;
                    }
                }

                return $defaultColor;
            }
        );
    }
}

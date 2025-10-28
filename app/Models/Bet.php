<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bet extends Model
{
    use CrudTrait;

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

   
}

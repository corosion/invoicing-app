<?php

namespace App\Model;

/**
 * @property string $name
 * @property float $rate
 */
class Currency extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'name' => 'string',
        'rate' => 'float'
    ];
}

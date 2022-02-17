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
        'name',
        'rate'
    ];

    /**
     * @param string $data
     * @return array
     */
    public static function prepareData(string $data)
    {
        list($name, $rate) = explode(':', $data);

        return [
            'name' => $name,
            'rate' => (float) $rate
        ];
    }
}

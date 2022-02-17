<?php

namespace App\Model;

/**
 * @property string $customer
 * @property string $vatNumber
 * @property string $documentNumber
 * @property int $type
 * @property string $parent
 * @property string $currency
 * @property float $total
 */
class Invoice extends Model
{
    const TYPE_INVOICE = 1;
    const TYPE_CREDIT_NOTE = 2;
    const TYPE_DEBIT_NOTE = 3;

    /**
     * @var array
     */
    protected $fillable = [
        'customer',
        'vatNumber',
        'documentNumber',
        'type',
        'parent',
        'currency',
        'total',
    ];

    /**
     * @return float|int
     */
    public function getTotal()
    {
        if($this->type !== Invoice::TYPE_CREDIT_NOTE) {
            return + $this->total;
        } else {
            return - $this->total;
        }
    }
}

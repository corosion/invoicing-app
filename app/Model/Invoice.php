<?php

namespace App\Model;

/**
 * @property string $customer
 * @property int $vat_number
 * @property string $document_number
 * @property int $type
 * @property string $parent_document
 * @property string $currency
 * @property float $total
 */
class Invoice extends Model
{
    const TYPE_INVOICE = 1;
    const TYPE_CREDIT_NOTE = 2;
    const TYPE_DEBIT_NOTE = 3;

    const TYPES = [
        Invoice::TYPE_INVOICE => 'Invoice',
        Invoice::TYPE_CREDIT_NOTE => 'Credit Note',
        Invoice::TYPE_DEBIT_NOTE => 'Debit Note',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'customer' => 'string',
        'vat_number' => 'int',
        'document_number' => 'string',
        'type' => 'int',
        'parent_document' => 'string',
        'currency' => 'string',
        'total' => 'float'
    ];

    /**
     * @return float
     */
    public function getTotal()
    {
        if (in_array($this->type, [Invoice::TYPE_INVOICE, Invoice::TYPE_DEBIT_NOTE])) {
            return +$this->total;
        } elseif ($this->type === Invoice::TYPE_CREDIT_NOTE) {
            return -$this->total;
        }

        return $this->total;
    }
}

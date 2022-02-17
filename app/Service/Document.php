<?php

namespace App\Service;

use App\Model\Currency;
use App\Model\Invoice;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

class Document
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $invoices;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $currencies;

    /**
     * @var Currency
     */
    protected $defaultCurrency;

    /**
     * @var \Illuminate\Translation\Translator
     */
    protected $translator;

    /**
     * @var \Illuminate\Validation\Factory
     */
    protected $validation;


    /**
     * @param \Illuminate\Validation\Factory $validation
     * @param \Illuminate\Translation\Translator $translator
     */
    public function __construct(
        Factory    $validation,
        Translator $translator
    ) {
        $this->validation = $validation;
        $this->translator = $translator;
    }

    /**
     * @param array $data
     * @return array|void
     */
    public function setData(array $data)
    {
        if ($errors = $this->validateData($data)) {
            return $errors;
        }

        $this->setCurrencies($data);
        $this->setInvoices($data);
        $this->defaultCurrency = $this->getCurrency('rate', 1);
    }

    /**
     * @param string $vatNumber
     * @return \Illuminate\Support\Collection
     */
    public function getTotals(string $vatNumber = '')
    {
        if (!empty($vatNumber)) {
            $this->invoices = $this->invoices;
        }

        return $this->invoices
            ->filter(function ($invoice) use ($vatNumber) {
                return !empty($vatNumber) ? ($invoice->vatNumber === $vatNumber) : true;
            })
            ->groupBy(function ($invoice) {
                return $invoice->customer;
            })->map(function ($invoiceGroup) {
                return $this->formatTotal(
                    $invoiceGroup->reduce(function ($total, $invoice) {
                        return $total + $this->covertTotal($invoice->getTotal(), $invoice->currency);
                    })
                );
            });
    }

    /**
     * @param array $data
     * @return array
     */
    protected function validateData(array $data)
    {
        return [];
    }

    /**
     * @param float $sum
     * @param string $from
     * @return float|int
     */
    protected function covertTotal(float $sum, string $from)
    {
        $currency = $this->getCurrency('name', $from);
        return $sum / $currency->rate;
    }

    /**
     * @param array $data
     * @return void
     */
    protected function setCurrencies(array $data)
    {
        $this->currencies = collect($data['currencies'])->map(function ($currency) {
            return (new Currency)->setData(Currency::prepareData($currency));
        });
    }

    /**
     * @param array $data
     * @return void
     */
    protected function setInvoices(array $data)
    {
        $this->invoices = collect($data['invoices'])->map(function ($invoice) {
            return (new Invoice)->setData([
                'customer' => $invoice['Customer'],
                'vatNumber' => $invoice['Vat number'],
                'number' => $invoice['Document number'],
                'type' => (int)$invoice['Type'],
                'total' => (float)$invoice['Total'],
                'currency' => $invoice['Currency'],
                'parent' => $invoice['Parent'] ?? null
            ]);
        });
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function getCurrency(string $key, $value)
    {
        return $this->currencies->first(function (Currency $currency) use ($key, $value) {
            return $currency->{$key} == $value;
        });
    }

    /**
     * @param float $total
     * @return string
     */
    protected function formatTotal(float $total)
    {
        return sprintf('%s %s', number_format($total, 2), $this->defaultCurrency->name);
    }
}

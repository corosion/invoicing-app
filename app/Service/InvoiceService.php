<?php

namespace App\Service;

use App\Model\Currency;
use App\Model\Invoice;
use Illuminate\Support\Collection;

class InvoiceService
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
     * @param array $data
     * @return void
     */
    public function setData(array $data)
    {
        $this->setCurrencies($data);
        $this->setInvoices($data);
        $this->defaultCurrency = $this->getCurrency('rate', 1);
    }

    /**
     * @param string|null $vatNumber
     * @return \Illuminate\Support\Collection
     */
    public function getTotals(string $vatNumber = null)
    {
        return $this->invoices
            ->filter(function ($invoice) use ($vatNumber) {
                return empty($vatNumber) || $invoice->vatNumber === $vatNumber;
            })
            ->groupBy(function (Invoice $invoice) {
                return $invoice->customer;
            })->map(function (Collection $invoiceGroup) {
                return $this->formatTotal(
                    $invoiceGroup->reduce(function ($total, Invoice $invoice) {
                        return $total + $this->covertTotal($invoice->getTotal(), $invoice->currency);
                    })
                );
            });
    }

    /**
     * @param array $data
     * @return void
     */
    public function setInvoices(array $data)
    {
        $this->invoices = collect($data['invoices'])->map(function ($invoice) {
            return (new Invoice)->setData([
                'customer' => $invoice['Customer'],
                'vatNumber' => $invoice['Vat number'],
                'number' => $invoice['Document number'],
                'type' => (int)$invoice['Type'],
                'total' => (float)$invoice['Total'],
                'currency' => $invoice['Currency'],
                'parent' => $invoice['Parent document'] ?? null
            ]);
        })->values();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * @param array $data
     * @return void
     */
    public function setCurrencies(array $data)
    {
        $this->currencies = collect($data['currencies'])->map(function ($currency) {
            return (new Currency)->setData(Currency::prepareData($currency));
        });
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function getCurrency(string $key, $value)
    {
        return $this->currencies->first(function (Currency $currency) use ($key, $value) {
            return $currency->{$key} == $value;
        });
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
     * @param float $total
     * @return string
     */
    protected function formatTotal(float $total)
    {
        return sprintf('%s %s', number_format($total, 2), $this->defaultCurrency->name);
    }
}

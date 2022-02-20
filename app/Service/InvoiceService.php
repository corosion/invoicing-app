<?php

namespace App\Service;

use App\Model\Currency;
use App\Model\Invoice;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
     * @var Currency
     */
    protected $outputCurrency;

    /**
     * @var \Illuminate\Validation\Factory
     */
    protected $validation;

    /**
     * @var \Illuminate\Translation\Translator
     */
    protected $translator;

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
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setData(array $data)
    {
        $this->setCurrencies($data);
        $this->setOutputCurrency($data);
        $this->setDefaultCurrency();
        $this->setInvoices($data);
    }

    /**
     * @param int|null $vatNumber
     * @return \Illuminate\Support\Collection
     */
    public function getTotals(int $vatNumber = null)
    {
        return $this->invoices
            ->filter(function ($invoice) use ($vatNumber) {
                return empty($vatNumber) || $invoice->vat_number === $vatNumber;
            })
            ->groupBy(function (Invoice $invoice) {
                return $invoice->customer;
            })
            ->map(function (Collection $invoiceGroup, $key) {
                return [
                    'customer' => $key,
                    'total' => $this->formatTotal(
                        $invoiceGroup->reduce(function ($total, Invoice $invoice) {
                            return $total + $this->covertTotal($invoice->getTotal());
                        })
                    )
                ];
            })->values();
    }

    /**
     * @param array $data
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setInvoices(array $data)
    {
        $invoices = $this->convertCsvDataKeys(collect($data['invoices']));
        $this->validateInvoices($invoices);

        $this->invoices = $invoices->map(function ($invoice) {
            return (new Invoice)->setData($invoice->toArray());
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
     * Validate csv document data
     * @param \Illuminate\Support\Collection $data
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateInvoices(Collection $data)
    {
        $validator = $this->validation->make($data->toArray(), [
            '*.customer' => 'required',
            '*.vat_number' => 'required',
            '*.document_number' => 'required',
            '*.type' => [
                'required',
                Rule::in([
                    Invoice::TYPE_INVOICE,
                    Invoice::TYPE_CREDIT_NOTE,
                    Invoice::TYPE_DEBIT_NOTE
                ])
            ],
            '*.parent_document' => [
                'nullable',
                function ($attribute, $value, $fail) use ($data) {
                    if ($data->pluck('document_number')->search($value) === false) {
                        $fail($this->translator->get('validation.document_number', [
                            'value' => $value
                        ]));
                    }
                }
            ],
            '*.currency' => [
                'required',
                Rule::in(
                    collect($this->currencies->toArray())
                        ->pluck('name')
                        ->toArray()
                )
            ],
            '*.total' => 'required'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param array $data
     * @return void
     */
    public function setCurrencies(array $data)
    {
        $this->currencies = collect($data['currencies'])->map(function ($currency) {
            list($name, $rate) = explode(':', $currency);
            return (new Currency)->setData([
                'name' => $name,
                'rate' => $rate
            ]);
        });
    }

    /**
     * @param array $data
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setOutputCurrency(array $data)
    {
        if (!$outputCurrency = $this->findCurrency('name', $data['output_currency'])) {
            throw ValidationException::withMessages([
                'output_currency' => $this->translator->get('validation.output_currency')
            ]);
        }

        $this->outputCurrency = $outputCurrency;
    }

    /**
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setDefaultCurrency()
    {
        if (!$defaultCurrency = $this->findCurrency('rate', 1)) {
            throw ValidationException::withMessages([
                'default_currency' => $this->translator->get('validation.default_currency')
            ]);
        }

        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function findCurrency(string $key, $value)
    {
        return $this->currencies->first(function (Currency $currency) use ($key, $value) {
            return $currency->{$key} == $value;
        });
    }

    /**
     * @param float $sum
     * @return float|int
     */
    protected function covertTotal(float $sum)
    {
        return $sum / $this->defaultCurrency->rate / $this->outputCurrency->rate;
    }

    /**
     * @param float $total
     * @return string
     */
    protected function formatTotal(float $total)
    {
        return sprintf('%s %s', number_format($total, 2), $this->outputCurrency->name);
    }

    /**
     * Convert csv data keys to snake case for validation
     * @param \Illuminate\Support\Collection $data
     * @return \Illuminate\Support\Collection
     */
    protected function convertCsvDataKeys(Collection $data)
    {
        return $data->map(function ($row) {
            return collect($row)->mapWithKeys(function ($value, $key) {
                return [Str::snake($key) => $value];
            });
        });
    }
}

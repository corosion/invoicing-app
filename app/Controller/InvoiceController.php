<?php

namespace App\Controller;

use App\Model\Invoice;
use App\Service\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use League\Csv\Reader;

class InvoiceController
{
    /**
     * @var \Illuminate\Translation\Translator
     */
    protected $translator;

    /**
     * @var \Illuminate\Validation\Factory
     */
    protected $validation;

    /**
     * @var \App\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @param \Illuminate\Validation\Factory $validation
     * @param \Illuminate\Translation\Translator $translator
     * @param \App\Service\InvoiceService $document
     */
    public function __construct(
        Factory        $validation,
        Translator     $translator,
        InvoiceService $document
    ) {
        $this->validation = $validation;
        $this->translator = $translator;
        $this->invoiceService = $document;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\JsonResponse $response
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \League\Csv\Exception|\Illuminate\Validation\ValidationException
     */
    public function create(Request $request, JsonResponse $response)
    {
        // return validation errors on fail
        if ($errors = $this->validateInput($request)) {
            throw ValidationException::withMessages($errors);
        }

        // Read the csv file
        $csv = Reader::createFromPath($request->allFiles()['csv_file']->path())
            ->setHeaderOffset(0);

        // return validation errors on fail
        if ($errors = $this->validateCSVData(collect($csv->getRecords()))) {
            throw ValidationException::withMessages($errors);
        }

        // Set invoices and currencies into document service
        $this->invoiceService->setData([
            'currencies' => $request->get('currency'),
            'invoices' => $csv->getRecords()
        ]);

        // Populate the response from the csv records
        return $response->setData([
            'invoices' => $this->invoiceService->getInvoices()->toArray(),
            'total' => $this->invoiceService->getTotals($request->get('vat_number'))
        ]);
    }

    /**
     * Validate csv file
     * @param \Illuminate\Http\Request $request
     * @return array|false
     */
    protected function validateInput(Request $request)
    {
        $validator = $this->validation->make($request->all(), [
            'currency' => 'required|array|min:1',
            'currency.*' => 'required|regex:/^([A-Z]){3}:[+-]?([0-9]*[.])?[0-9]+$/',
            'csv_file' => [
                'required',
                'file',
                function ($attribute, $value, $fails) {
                    if ($value->getClientOriginalExtension() !== 'csv') {
                        $fails($this->translator->get('validation.mimes', [
                            'values' => 'csv'
                        ]));
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            return $validator->errors()->toArray();
        } else {
            return false;
        }
    }

    /**
     * Validate csv document data
     * @param \Illuminate\Support\Collection $data
     * @return array|false
     */
    protected function validateCSVData(Collection $data)
    {
        $validator = $this->validation->make($this->csvDataKeysToSnake($data)->toArray(), [
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
                    if ($data->pluck('Document number')->search($value) === false) {
                        $fail($this->translator->get('validation.document_number', [
                            'value' => $value
                        ]));
                    }
                }
            ],
            '*.currency' => 'required',
            '*.total' => 'required'
        ]);

        if ($validator->fails()) {
            return $validator->errors()->toArray();
        } else {
            return false;
        }
    }

    /**
     * Convert csv data keys to snake case for validation
     * @param \Illuminate\Support\Collection $data
     * @return \Illuminate\Support\Collection
     */
    protected function csvDataKeysToSnake(Collection $data)
    {
        return $data->map(function ($row) {
            return collect($row)->mapWithKeys(function ($value, $key) {
                return [Str::snake($key) => $value];
            });
        });
    }
}

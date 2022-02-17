<?php

namespace App\Controller;

use App\Service\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
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
     * @param \App\Service\InvoiceService $invoiceService
     */
    public function __construct(
        Factory        $validation,
        Translator     $translator,
        InvoiceService $invoiceService
    ) {
        $this->validation = $validation;
        $this->translator = $translator;
        $this->invoiceService = $invoiceService;
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
        $this->validateInput($request);

        // Read the csv file
        $csv = Reader::createFromPath($request->allFiles()['csv_file']->path())
            ->setHeaderOffset(0);

        // Set invoices and currencies into document service
        $this->invoiceService->setData([
            'output_currency' => $request->get('output_currency'),
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
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateInput(Request $request)
    {
        $validator = $this->validation->make($request->all(), [
            'output_currency' => 'required',
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
            throw new ValidationException($validator);
        }
    }
}

<?php

namespace App\Controller;

use App\Model\Currency;
use App\Model\Invoice;
use App\Service\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Rule;
use League\Csv\Reader;

class DocumentController
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
     * @var \App\Service\Document
     */
    protected $document;

    /**
     * @param \Illuminate\Validation\Factory $validation
     * @param \Illuminate\Translation\Translator $translator
     * @param \App\Service\Document $document
     */
    public function __construct(
        Factory    $validation,
        Translator $translator,
        Document   $document
    )
    {
        $this->validation = $validation;
        $this->translator = $translator;
        $this->document = $document;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\JsonResponse $response
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \League\Csv\Exception
     */
    public function create(Request $request, JsonResponse $response)
    {
        // return validation errors on fail
        if ($errors = $this->validateFile($request)) {
            return $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setData($errors);
        }

        // Read the csv file
        $csv = Reader::createFromPath($request->allFiles()['csv_file']->path())
            ->setHeaderOffset(0);

        // return validation errors on fail
        if ($errors = $this->validateCSVData(collect($csv->getRecords()))) {
            return $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setData($errors);
        }

        // Set invoices and currencies into document service
        $this->document->setData([
            'currencies' => $request->get('currency'),
            'invoices' => $csv->getRecords()
        ]);

        // Populate the response from the csv records
        return $response->setData([
            'invoices' => $this->document->getInvoices()->toArray(),
            'total' => $this->document->getTotals()
        ]);
    }

    /**
     * Validate csv file
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\MessageBag|void
     */
    protected function validateFile(Request $request)
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
            return $validator->errors();
        }
    }

    /**
     * Validate csv document data
     * @param \Illuminate\Support\Collection $data
     * @return \Illuminate\Support\Collection|void
     */
    protected function validateCSVData(Collection $data)
    {
        $errors = $data->map(function ($row, $number) use ($data) {
            $validator = $this->validation->make($row, [
                'Customer' => 'required',
                'Vat number' => 'required',
                'Document number' => 'required',
                'Type' => [
                    'required',
                    Rule::in([
                        Invoice::TYPE_INVOICE,
                        Invoice::TYPE_CREDIT_NOTE,
                        Invoice::TYPE_DEBIT_NOTE
                    ])
                ],
                'Parent document' => [
                    'nullable',
                    function ($attribute, $value, $fail) use ($data) {
                        if ($data->pluck('Document number')->search($value) === false) {
                            $fail($this->translator->get('validation.document_number', [
                                'value' => $value
                            ]));
                        }
                    }
                ],
                'Currency' => 'required',
                'Total' => 'required'
            ]);

            if ($validator->fails()) {
                return [
                    'rowNumber' => $number,
                    'errors' => $validator->errors()
                ];
            }
        })->filter()->values();

        if ($errors->isNotEmpty()) {
            return $errors;
        }
    }
}

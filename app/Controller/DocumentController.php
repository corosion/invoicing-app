<?php

namespace App\Controller;

use App\Model\Currency;
use App\Model\Invoice;
use App\Service\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
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
     * @var \App\Service\Document
     */
    protected $document;

    /**
     * @param \Illuminate\Validation\Factory $validation
     * @param \Illuminate\Translation\Translator $translator
     * @param \App\Service\Document $document
     */
    public function __construct(
        Factory $validation,
        Translator $translator,
        Document $document
    ) {
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
        // Create validator with file specific validations
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

        // return validation errors on fail
        if ($validator->fails()) {
            return $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setData($validator->errors());
        }

        // Read the csv file
        $csv = Reader::createFromPath($request->allFiles()['csv_file']->path())
            ->setHeaderOffset(0);

        $this->document->setData([
            'currencies' => $request->get('currency'),
            'invoices' => $csv->getRecords()
        ]);

        die();


        $currencies = collect($request->get('currency'))->map(function ($currency) {
            return (new Currency)->setData(Currency::prepareData($currency));
        });

        $defaultCurrency = $currencies->firstWhere('rate', 1);
        var_dump($defaultCurrency);
        die();
        $invoices = collect($csv->getRecords())->map(function ($invoice) {
            var_dump($invoice);
            return (new Invoice())->setData($invoice);
        });
        var_dump(
            $invoices[0]
        );

        die();
        // Populate the response from the csv records
        return $response->setData(
            collect($csv->getRecords())
                ->values()
        );
    }
}

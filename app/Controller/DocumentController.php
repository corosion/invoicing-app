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
        // return validation errors on fail
        if ($errors = $this->validateFile($request)) {
            return $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setData($errors);
        }

        // Read the csv file
        $csv = Reader::createFromPath($request->allFiles()['csv_file']->path())
            ->setHeaderOffset(0);

        // Set invoices and currencies into document service
        if ($errors = $this->document->setData([
            'currencies' => $request->get('currency'),
            'invoices' => $csv->getRecords()
        ])) {
            return $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setData($errors);
        }

        // Populate the response from the csv records
        return $response->setData(
            $this->document->getTotals()
        );
    }

    /**
     * Validate csv file
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\MessageBag|void
     */
    protected function validateFile(Request $request)
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
            return $validator->errors();
        }
    }
}

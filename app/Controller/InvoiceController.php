<?php

namespace App\Controller;

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
     * @param \Illuminate\Validation\Factory $validation
     * @param \Illuminate\Translation\Translator $translator
     */
    public function __construct(Factory $validation, Translator $translator)
    {
        $this->validation = $validation;
        $this->translator = $translator;
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

        // Populate the response from the csv records
        return $response->setData(
            collect($csv->getRecords())
                ->values()
        );
    }
}

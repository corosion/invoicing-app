<?php

namespace App\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

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
     */
    public function create(Request $request, JsonResponse $response)
    {

    }
}

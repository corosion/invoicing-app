<?php

namespace App\Service;

use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use PHPUnit\Framework\TestCase;

class InvoiceServiceTest extends TestCase
{

    /**
     * @return \App\Service\InvoiceService
     */
    private function boot()
    {
        $validation = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new InvoiceService($validation, $translator);
    }

    public function testGetCurrency()
    {
        $invoiceService = $this->boot();
    }

    public function testFormatTotal()
    {
        $invoiceService = $this->boot();
    }

    public function testSetDefaultCurrency()
    {
        $invoiceService = $this->boot();
    }
}

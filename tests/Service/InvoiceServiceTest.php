<?php

namespace App\Service;

use App\Model\Currency;
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

    /**
     * @param $object
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    private function callMethod($object, string $method , array $parameters = [])
    {
        try {
            $className = get_class($object);
            $reflection = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new \Exception($e->getMessage());
        }

        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testGetCurrency()
    {
        $invoiceService = $this->boot();

        $mock = $this->createMock(InvoiceService::class);
        $mock->method('getCurrency')->willReturn(
            (new Currency())->setData([
                'name' => 'BGN',
                'rate' => 2.5
            ])
        );


    }

    public function testFormatTotal()
    {
        $invoiceService = $this->boot();
        //$this->assertEquals('420.00 BGN', $this->callMethod($mock, 'formatTotal' , [ 'total' => 420]));
    }

    public function testSetDefaultCurrency()
    {
        $invoiceService = $this->boot();

        // $this->assertEquals('420.00 BGN', $this->callMethod($invoiceService, 'formatTotal' , [ 'total' => 420]));
    }
}

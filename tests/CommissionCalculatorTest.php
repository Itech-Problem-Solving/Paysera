<?php

use roman\CommissionCalculator;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    protected $CommissionCalculator;

    public function setUp():void
    {
        $this->CommissionCalculator = new CommissionCalculator();
    }

    public function testcalculateCommissionsFromFile()
    {
        $filePath = __DIR__ . '/../input.txt';
        $expectedOutput = "1\n0.442857636\n1.528234394";
        ob_start();
        // $this->CommissionCalculator->calculateCommissionsFromFile($filePath);
        $commissionCalculator = new CommissionCalculator();
        $commissionCalculator->calculateCommissionsFromFile($filePath);
        $output = ob_get_clean();

        $this->assertEquals($expectedOutput, $output);
    }
}
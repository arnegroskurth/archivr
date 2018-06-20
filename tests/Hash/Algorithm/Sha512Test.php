<?php

namespace Storeman\Test\Hash\Algorithm;

use Storeman\Hash\Algorithm\HashAlgorithmInterface;
use Storeman\Hash\Algorithm\Sha512;

class Sha512Test extends AbstractAlgorithmTest
{
    protected function getTestCases(): array
    {
        return [
            'Hello World' => '2c74fd17edafd80e8447b0d46741ee243b7eb74dd2149a0ab1b9246fb30382f27e853d8585719e0e67cbda0daa8f51671064615d645ae27acb15bfb1447f459b',
        ];
    }

    protected function getFunction(): HashAlgorithmInterface
    {
        return new Sha512();
    }
}
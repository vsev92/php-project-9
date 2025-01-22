<?php

use PHPUnit\Framework\TestCase;
use App\DIConfigurator;
use App\DbConnector;

final class AnalyzerTest extends TestCase
{

    protected function setUp(): void
    {
        $configurator = new DIConfigurator()
        $dbUrl = $configurator->getTest
        $connector = new DbConnector(new DIConfigurator())
        $this->example = new Example(
            $this->createStub(Collaborator::class)
        );
    }

    protected function tearDown(): void
    {
        $this->example = null;
    }
    public function testGreetsWithName(): void
    {


        
    }
}
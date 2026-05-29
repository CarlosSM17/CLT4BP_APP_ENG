<?php

use App\Services\MslqProcessorService;
use Tests\TestCase;

class MslqProcessorServiceTest extends TestCase
{
    private MslqProcessorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MslqProcessorService();
    }

    public function test_processes_mslq_responses_correctly()
    {
        $responses = [
            1 => 6, 16 => 5, 22 => 7, 24 => 6, // intrinsic_goal_orientation
            5 => 5, 6 => 6, 12 => 5, 15 => 6, 20 => 7, 21 => 6, 29 => 5, 31 => 6 // self_efficacy
        ];

        $result = $this->service->processResponses($responses);

        $this->assertArrayHasKey('motivation', $result);
        $this->assertArrayHasKey('strategies', $result);
        $this->assertArrayHasKey('summary', $result);

        $this->assertEquals('alto', $result['motivation']['intrinsic_goal_orientation']['level']);
    }

    public function test_determines_level_correctly()
    {
        // Test reflection para método privado
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('determineLevel');
        $method->setAccessible(true);

        $this->assertEquals('alto', $method->invoke($this->service, 6.0));
        $this->assertEquals('medio', $method->invoke($this->service, 4.5));
        $this->assertEquals('bajo', $method->invoke($this->service, 2.0));
    }
}

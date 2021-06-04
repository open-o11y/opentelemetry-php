<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Contrib as Path;
use OpenTelemetry\Sdk\Trace\ExporterFactory as ExporterFactory;
use PHPUnit\Framework\TestCase;


class ExporterFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function testIfExporterHasCorrectEndpoint()
    {
        putenv('ENDPOINT=zipkin+http://zipkin:9411/api/v2/spans');
        $factory = new ExporterFactory('test.zipkin');
        $exporter = $factory->fromConnectionString(getenv('ENDPOINT'));
        $this->assertInstanceOf(Path\Zipkin\Exporter::class, $exporter);
        
       
        putenv('ENDPOINT=jaeger+http://jaeger:9412/api/v2/spans');
        $factory = new ExporterFactory('test.jaeger');
        $exporter = $factory->fromConnectionString(getenv('ENDPOINT'));
        $this->assertInstanceOf(Path\Jaeger\Exporter::class, $exporter);
        
    }
}

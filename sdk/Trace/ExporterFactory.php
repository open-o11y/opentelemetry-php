<?php

declare(strict_types=1);
namespace OpenTelemetry\Sdk\Trace;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

class ExporterFactory {

    private $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

   /**
     * Selects the correct Exporter via the configuration string
     * Currently only supports Jaeger and Zipkin exporters
     *
     * @param string $configurationString String containing unextracted information for Exporter creation
     * @return Exporter dynamically chosen exporter based on the specifications in the input string
     */
    public function fromConnectionString(string $configurationString) : Exporter
    {
        $strArr = explode("+", $configurationString);
        $contribName = $strArr[0];
        $endpointUrl = $strArr[1];
        $dynamicExporter = "OpenTelemetry\Contrib\\" . ucfirst($contribName) . "\Exporter";

        $exporter = new $dynamicExporter(
            $this->name,
            $endpointUrl,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );
        return $exporter;
    }

}

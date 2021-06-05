<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

class ExporterFactory
{
    private $name;
    private $_allowedExporters = ['Jaeger' => true, 'Zipkin' => true, 'Newrelic' => true, 'Otlp' => true, 'Otlpgrpc' => true, 'Zipkintonewrelic' => true];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
      * Selects the correct Exporter via the configuration string
      * Currently only supports Jaeger and Zipkin exporters
      *
      * @param string $configurationString String containing unextracted information for Exporter creation
      */
    public function fromConnectionString(string $configurationString)
    {
        $strArr = htmlentities($configurationString);
        $strArr = explode('+', $strArr);
        if (sizeof($strArr) != 2) {
            return null;
        }

        $contribName = strtolower($strArr[0]);
        $endpointUrl = $strArr[1];

        if (!$this->_isAllowed(ucfirst($contribName))) {
            return null;
        }

        $dynamicExporter = $this->_contribNameToPath($contribName);
        switch ($contribName) {
            case 'jaeger':
                return $exporter = $this->_generateJaeger($endpointUrl, $dynamicExporter);
            case 'zipkin':
                return $exporter = $this->_generateZipkin($endpointUrl, $dynamicExporter);
            case 'newrelic':
                return $exporter = $this->_generateNewrelic($endpointUrl, $dynamicExporter);
            case 'otlp':
                return $exporter = $this->_generateOtlp($dynamicExporter);
            case 'otlpgrpc':
                return $exporter = $this->_generateOtlpGrpc($dynamicExporter);
            case 'zipkintonewrelic':
                return $exporter = $this->_generateZipkinToNewrelic($endpointUrl, $dynamicExporter);
            default:
                return null;
            }
    }

    private function _isAllowed(string $exporter)
    {
        return array_key_exists($exporter, $this->_allowedExporters) && $this->_allowedExporters[$exporter];
    }

    // Handles paths with capitilized letters
    private function _contribNameToPath(string $contribName)
    {
        if ($contribName == 'zipkintonewrelic') {
            return "OpenTelemetry\Contrib\ZipkinToNewrelic\Exporter";
        }
        if ($contribName == 'otlpgrpc') {
            return "OpenTelemetry\Contrib\OtlpGrpc\Exporter";
        }

        return "OpenTelemetry\Contrib\\" . ucfirst($contribName) . "\Exporter";
    }

    private function _generateJaeger(string $endpointUrl, string $dynamicExporter)
    {
        $exporter = new $dynamicExporter(
            $this->name,
            $endpointUrl,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }
    private function _generateZipkin(string $endpointUrl, string $dynamicExporter)
    {
        $exporter = new $dynamicExporter(
            $this->name,
            $endpointUrl,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }
    private function _generateNewrelic(string $endpointUrl, string $dynamicExporter)
    {
        $licenseKey = getenv('NEW_RELIC_INSERT_KEY');
        $exporter = new $dynamicExporter(
            $this->name,
            $endpointUrl,
            $licenseKey,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }

    private function _generateOtlp(string $dynamicExporter)
    {
        $exporter = new $dynamicExporter(
            $this->name,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }

    private function _generateOtlpGrpc(string $dynamicExporter)
    {
        return new $dynamicExporter();
    }
   
    private function _generateZipkinToNewrelic(string $endpointUrl, $dynamicExporter)
    {
        $licenseKey = getenv('NEW_RELIC_INSERT_KEY');
        $exporter = new $dynamicExporter(
            $this->name,
            $endpointUrl,
            $licenseKey,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }
}

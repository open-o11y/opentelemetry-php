<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Nyholm\Dsn\DsnParser;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Newrelic\Exporter as NewrelicExporter;
use OpenTelemetry\Contrib\Otlp\Exporter as OtlpExporter;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OtlpGrpcExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\Contrib\ZipkinToNewrelic\Exporter as ZipkinToNewrelicExporter;

class ExporterFactory
{
    private $name;
    private $_allowedExporters = ['jaeger' => true, 'zipkin' => true, 'newrelic' => true, 'otlp' => true, 'otlpgrpc' => true, 'zipkintonewrelic' => true];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
      * Returns the coresponding Exporter via the configuration string
      *
      * @param string $configurationString String containing unextracted information for Exporter creation
      * Should follow the format: contribType+baseUrl?option1=a
      * Query string is optional and based on the Exporter
      */
    public function fromConnectionString(string $configurationString)
    {
        $strArr = explode('+', $configurationString);
        // checks if input is given with the format type+baseUrl
        if (sizeof($strArr) != 2) {
            return null;
        }

        $contribName = strtolower($strArr[0]);
        $endpointUrl = $strArr[1];

        if (!$this->_isAllowed($contribName)) {
            return null;
        }

        // endpointUrl should only be null with otlp and otlpgrpc
        $licenseKey = '';
        if ($endpointUrl != false) {
            $dsn = DsnParser::parse($endpointUrl);
            $endpointUrl = (string) ($dsn->withoutParameter('licenseKey'));
            $licenseKey = (string) ($dsn->getParameter('licenseKey'));
        }
 
        switch ($contribName) {
            case 'jaeger':
                return $exporter = $this->_generateJaeger($endpointUrl);
            case 'zipkin':
                return $exporter = $this->_generateZipkin($endpointUrl);
            case 'newrelic':
                return $exporter = $this->_generateNewrelic($endpointUrl, $licenseKey);
            case 'otlp':
                return $exporter = $this->_generateOtlp();
            case 'otlpgrpc':
                return $exporter = $this->_generateOtlpGrpc();
            case 'zipkintonewrelic':
                return $exporter = $this->_generateZipkinToNewrelic($endpointUrl, $licenseKey);
            }
    }

    private function _isAllowed(string $exporter)
    {
        return array_key_exists($exporter, $this->_allowedExporters) && $this->_allowedExporters[$exporter];
    }

    private function _generateJaeger(string $endpointUrl)
    {
        $exporter = new JaegerExporter(
            $this->name,
            $endpointUrl,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }
    private function _generateZipkin(string $endpointUrl)
    {
        $exporter = new ZipkinExporter(
            $this->name,
            $endpointUrl,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }
    private function _generateNewrelic(string $endpointUrl, string $licenseKey)
    {
        if ($licenseKey == false) {
            return null;
        }
        $exporter = new NewrelicExporter(
            $this->name,
            $endpointUrl,
            $licenseKey,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }

    private function _generateOtlp()
    {
        $exporter = new OtlpExporter(
            $this->name,
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        return $exporter;
    }

    private function _generateOtlpGrpc()
    {
        return new OtlpGrpcExporter();
    }
   
    private function _generateZipkinToNewrelic(string $endpointUrl, string $licenseKey)
    {
        if ($licenseKey == false) {
            return null;
        }
        $exporter = new ZipkinToNewrelicExporter(
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

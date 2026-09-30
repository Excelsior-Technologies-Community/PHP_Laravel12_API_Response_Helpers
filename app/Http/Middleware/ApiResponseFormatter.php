<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseFormatter
{
    /**
     * Handle an incoming request and format API response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $response = $next($request);
        $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);
        $memoryUsageKb = round(memory_get_usage() / 1024, 2);

        // Add performance headers
        $response->headers->set('X-Execution-Time-Ms', (string) $executionTimeMs);
        $response->headers->set('X-Memory-Usage-Kb', (string) $memoryUsageKb);

        $format = strtolower($request->get('format', 'json'));

        if ($format === 'json' && $response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);
            if (is_array($data)) {
                $data['_benchmark'] = [
                    'execution_time_ms' => $executionTimeMs,
                    'memory_usage_kb' => $memoryUsageKb,
                ];
                $response->setData($data);
            }
            return $response;
        }

        if ($format === 'xml') {
            $content = $response->getContent();
            $data = json_decode($content, true) ?? ['content' => $content];
            $xmlData = $this->arrayToXml(['response' => $data]);

            return response($xmlData, $response->getStatusCode(), [
                'Content-Type' => 'application/xml',
                'X-Execution-Time-Ms' => (string) $executionTimeMs,
            ]);
        }

        if ($format === 'csv') {
            $content = $response->getContent();
            $data = json_decode($content, true);
            $items = $data['data'] ?? ($data['products'] ?? (is_array($data) ? $data : []));

            $csvData = $this->arrayToCsv($items);

            return response($csvData, $response->getStatusCode(), [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'inline; filename="api-response.csv"',
                'X-Execution-Time-Ms' => (string) $executionTimeMs,
            ]);
        }

        return $response;
    }

    /**
     * Convert array to XML string.
     */
    private function arrayToXml(array $data, \SimpleXMLElement $xml = null): string
    {
        if ($xml === null) {
            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><api_response/>');
        }

        foreach ($data as $key => $value) {
            $key = is_numeric($key) ? 'item_' . $key : preg_replace('/[^a-z0-9_]/i', '_', $key);

            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }

        return $xml->asXML();
    }

    /**
     * Convert array of items to CSV string.
     */
    private function arrayToCsv(array $items): string
    {
        if (empty($items)) {
            return "message\nNo data available";
        }

        // Handle single array item
        if (isset($items['id']) || isset($items['name'])) {
            $items = [$items];
        }

        $output = fopen('php://temp', 'r+');

        if (isset($items[0]) && is_array($items[0])) {
            fputcsv($output, array_keys($items[0]));
            foreach ($items as $row) {
                if (is_array($row)) {
                    $cleanRow = array_map(function($val) {
                        return is_array($val) ? json_encode($val) : $val;
                    }, $row);
                    fputcsv($output, $cleanRow);
                }
            }
        } else {
            fputcsv($output, ['key', 'value']);
            foreach ($items as $k => $v) {
                fputcsv($output, [$k, is_array($v) ? json_encode($v) : $v]);
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}

<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScanGeoResolver
{
    /**
     * @return array{country: ?string, city: ?string, source: string}
     */
    public function resolve(Request $request): array
    {
        $country = $this->firstHeader($request, [
            'cf-ipcountry',
            'x-vercel-ip-country',
            'x-appengine-country',
            'cloudfront-viewer-country',
        ]);
        $city = $this->firstHeader($request, [
            'x-vercel-ip-city',
            'x-appengine-city',
            'cloudfront-viewer-city',
        ]);

        return [
            'country' => $this->normalize($country),
            'city' => $this->normalize($city),
            'source' => $country || $city ? 'edge_headers' : 'unavailable',
        ];
    }

    /**
     * @param  string[]  $headers
     */
    private function firstHeader(Request $request, array $headers): ?string
    {
        foreach ($headers as $header) {
            $value = $request->headers->get($header);

            if (is_string($value) && trim($value) !== '') {
                return urldecode($value);
            }
        }

        return null;
    }

    private function normalize(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || Str::lower($value) === 'unknown') {
            return null;
        }

        return Str::limit($value, 120, '');
    }
}

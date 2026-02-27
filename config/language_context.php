<?php

return [
    'country_headers' => array_values(array_filter(array_map(
        static fn (string $header): string => trim($header),
        explode(',', (string) env('LANGUAGE_COUNTRY_HEADERS', 'CF-IPCountry,CloudFront-Viewer-Country,X-Country-Code'))
    ))),
];

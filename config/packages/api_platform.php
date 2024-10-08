<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('api_platform', [
        'title' => 'LifeOS',
        'version' => '1.0.0',
        'formats' => [
            'jsonld' => [
                'application/ld+json',
            ],
        ],
        'docs_formats' => [
            'jsonld' => [
                'application/ld+json',
            ],
            'jsonopenapi' => [
                'application/vnd.openapi+json',
            ],
            'html' => [
                'text/html',
            ],
        ],
        'defaults' => [
            'stateless' => true,
            'cache_headers' => [
                'vary' => [
                    'Content-Type',
                    'Authorization',
                    'Origin',
                ],
            ],
            'extra_properties' => [
                'standard_put' => true,
                'rfc_7807_compliant_errors' => true,
            ],
        ],
        'use_symfony_listeners' => true,
    ]);
};

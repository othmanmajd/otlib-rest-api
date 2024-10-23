<?php

declare(strict_types=1);

return [
    'frontend' => [
        'otlib/rest-api' => [
            'target' => \Otlib\RestApi\Middleware\OtlibApiMiddleware::class,
            'before' => [
                'typo3/cms-redirects/redirecthandler',
            ],
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
        ],
    ],
];

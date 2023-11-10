<?php

namespace Reptily\ApiCheck\Tests;

use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\TestCase;
use Reptily\ApiCheck\ApiCheck;

class Tests extends TestCase
{
    public function testSuccessStructure(): void
    {
        $response = [
            'id' => 1,
            'data' => [
                'names' => ['car', 'foot', 'ball']
            ],
            'color' => [
                [
                    'id' => 1,
                    'name' => 'red',
                ],
            ],
            'cars' => [
                [
                    'models' => [
                        [
                            'type' => 'sedan',
                            'size' => 8
                        ],
                        [
                            'type' => 'sedan',
                            'size' => 8
                        ],
                    ],
                ],
            ],
            'is_error' => false,
            'text' => null,
        ];

        $structure = [
            'id' => ApiCheck::TYPE_INTEGER,
            'data' => [
                'names' => [
                    ApiCheck::TYPE_STRING,
                ],
            ],
            'color' => [
                [
                    'id' => ApiCheck::TYPE_INTEGER,
                    'name' => ApiCheck::TYPE_STRING,
                ]
            ],
            'cars' => [
                [
                    'models' => [
                        [
                            'type' => ApiCheck::TYPE_STRING,
                            'size' => ApiCheck::TYPE_INTEGER,
                        ]
                    ]
                ]
            ],
            'is_error' => ApiCheck::TYPE_BOOLEAN,
            'text' => ApiCheck::TYPE_NULL,
        ];

        $this->assertTrue(ApiCheck::structure($structure, $response));
    }

    public function testErrorStructure(): void
    {
        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('info')->andReturnNull();
        Log::shouldReceive('error')->andReturnNull();

        $this->assertFalse(ApiCheck::structure(
            ['id' => ApiCheck::TYPE_INTEGER],
            ['id' => 'aaaa']
        ));

        $this->assertFalse(ApiCheck::structure(
            ['id' => ApiCheck::TYPE_INTEGER],
            ['id' => 1.01]
        ));

        $this->assertFalse(ApiCheck::structure(
            ['id' => ApiCheck::TYPE_INTEGER],
            ['id' => '1']
        ));

        $this->assertFalse(ApiCheck::structure(
            ['id' => ApiCheck::TYPE_DOUBLE],
            ['id' => 1]
        ));

        $this->assertFalse(ApiCheck::structure(
            ['id' => ApiCheck::TYPE_DOUBLE],
            ['id' => '1.01']
        ));

        $this->assertFalse(ApiCheck::structure(
            ['id' => ApiCheck::TYPE_DOUBLE],
            ['id' => 'aaaa']
        ));

        $this->assertFalse(ApiCheck::structure(
            ['id' => ApiCheck::TYPE_NUMERIC],
            ['id' => 'aaaa']
        ));

        $this->assertFalse(ApiCheck::structure(
            ['name' => ApiCheck::TYPE_STRING],
            ['name' => null]
        ));

        $this->assertFalse(ApiCheck::structure(
            ['name' => ApiCheck::TYPE_STRING],
            ['name' => 123]
        ));

        $this->assertFalse(ApiCheck::structure(
            ['name' => ApiCheck::TYPE_STRING],
            ['name' => true]
        ));

        $this->assertFalse(ApiCheck::structure(
            ['is_active' => ApiCheck::TYPE_BOOLEAN],
            ['is_active' => null]
        ));

        $this->assertFalse(ApiCheck::structure(
            ['is_active' => ApiCheck::TYPE_BOOLEAN],
            ['is_active' => 'true']
        ));

        $this->assertFalse(ApiCheck::structure(
            ['is_active' => ApiCheck::TYPE_BOOLEAN],
            ['is_active' => 1]
        ));

        $this->assertFalse(ApiCheck::structure(
            ['is_active' => ApiCheck::TYPE_BOOLEAN],
            ['is_active' => '1']
        ));

        $this->assertFalse(ApiCheck::structure(
            ['items' => [
                ApiCheck::TYPE_ARRAYS => [
                    "id" => ApiCheck::TYPE_INTEGER,
                ]
            ]],
            ['items' => [['id' => '111']]]
        ));

        $this->assertFalse(ApiCheck::structure(
            ['items' => [
                ApiCheck::TYPE_ARRAYS => [
                    "id" => ApiCheck::TYPE_INTEGER,
                ]
            ]],
            ['items' => null]
        ));

        $this->assertFalse(ApiCheck::structure(
            ['items' => [
                ApiCheck::TYPE_ARRAYS => [
                    "id" => ApiCheck::TYPE_INTEGER,
                ]
            ]],
            ['items' => []]
        ));
    }

    public function testType(): void
    {
        $structure = [
            'id' => ApiCheck::TYPE_INTEGER,
            'name' => ApiCheck::TYPE_STRING,
            'amount' => ApiCheck::TYPE_DOUBLE,
            'is_active' => ApiCheck::TYPE_BOOLEAN,
            'page' => ApiCheck::TYPE_NUMERIC,
            'percent' => ApiCheck::TYPE_NUMERIC,
        ];

        $data = [
            'id' => 1,
            'name' => 'Bob',
            'amount' => 110.01,
            'is_active' => true,
            'page' => 3,
            'percent' => 50.01
        ];

        $this->assertTrue(ApiCheck::structure($structure, $data));
    }
}
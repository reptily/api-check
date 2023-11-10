<?php

namespace Reptily\ApiCheck;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class ApiCheck
{
    public const FIELD_CLASS = 'class';
    public const FIELD_FUNCTION = 'function';
    public const FIELD_ARGUMENTS = 'arguments';

    public const TYPE_STRING = 'string';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_DOUBLE = 'double';
    public const TYPE_NUMERIC = 'numeric';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_ARRAYS = self::class . '_arrays';
    public const TYPE_NULL = 'NULL';

    public static function structure($rules, $data, $placeAlert = ''): bool
    {
        if (!is_array($rules)) {
            if ($rules === self::TYPE_NUMERIC) {
                $result = is_numeric($data);
            } else {
                $result = gettype($data) === $rules;
            }

            if (!$result) {
                $whereCall = self::whereCall();
                self::logstash('Api check structure type error: ' . $placeAlert, $whereCall);
            }

            return $result;
        }

        foreach ($rules as $key => $value) {
            if ($key === ApiCheck::TYPE_ARRAYS) {
                if (empty($data)) {
                    $whereCall = self::whereCall();
                    self::logstash('Api check structure array empty: ' . $placeAlert, $whereCall);

                    return false;
                }

                foreach ($data as $datum) {
                    if (!self::structure($rules[ApiCheck::TYPE_ARRAYS], $datum, $placeAlert . '[]')) {
                        return false;
                    }
                }
                continue;
            }

            if (!array_key_exists($key, $data)) {
                $whereCall = self::whereCall();
                self::logstash('Api check structure error: ' . $placeAlert . '.' . $key, $whereCall);

                return false;
            };

            if (!self::structure($value, $data[$key], $placeAlert . '.' . $key)) {
                return false;
            }
        }

        return true;
    }

    public static function response(Response $response): bool
    {
        $whereCall = self::whereCall();
        if ($response->status() !== 200) {
            self::logstash('Api status error status: %s ' . $response->status(), $whereCall);

            return false;
        }

        $json = $response->json() ?? [];
        if (isset($json['error']) && $json['error'] == true) {
            self::logstash('Api status error status text: %s ' . $json['errorText'] ?? '', $whereCall);

            return false;
        }

        return true;
    }

    public static function checker($response, array $rules): bool
    {
        return self::response($response) && self::structure($rules, $response->json() ?? []);
    }

    private static function whereCall(): array
    {
        $trace = debug_backtrace();
        $caller = [];

        foreach ($trace as $i => $item) {
            if(isset($item['class']) && $item['class'] == self::class) {
                $caller = $trace[++$i];
            }
        }

        return [
            self::FIELD_CLASS => isset($caller['class']) ? $caller['class'] : null,
            self::FIELD_FUNCTION => isset($caller['function']) ? $caller['function'] : null,
            self::FIELD_ARGUMENTS => isset($caller['args']) ? $caller['args'] : null,
        ];
    }

    private static function logstash(string $text, array $whereCall): void
    {
        Log::error(sprintf(
            '%s %s',
            $whereCall[self::FIELD_CLASS] . '::' . $whereCall[self::FIELD_FUNCTION],
            $text
        ));
    }
}

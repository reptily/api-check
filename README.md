# Helper class for checking the API structure of external services

## Install
```bash
composer require reptily/api-check
```

## what problem does this helper solve?

Laravel façade doesn't handle big data well

#### Large Array Example
```php
$users = [];
$user = [
    'id' => 1,
    'name' => 'Bob',
    'is_active' => true,
    'books' => []
];
$book = [
    'id' => 1,
    'name' => 'test book',
];

for ($i=0; $i < 100; $i++) {
    $user['books'][] = $book;
}

for ($i=0; $i < 500; $i++) {
    $users[] = $user;
}
```

#### benchmark facade Validator
```php
Validator::make($users, [
    '*.id' => ['required', 'integer'],
    '*.name' => ['required', 'string'],
    '*.is_active' => ['required', 'boolean'],
    '*.books' => ['required', 'array'],
    '*.books.*.id' => ['required', 'integer'],
    '*.books.*.name' => ['required', 'string'],
]);
```

```bash
End time: 84.0274 sec.
```

#### benchmark ApiCheck
```php
ApiCheck::structure([
    ApiCheck::TYPE_ARRAYS => [
        'id' => ApiCheck::TYPE_INTEGER,
        'name' => ApiCheck::TYPE_STRING,
        'is_active' => ApiCheck::TYPE_BOOLEAN,
        'books' => [
            ApiCheck::TYPE_ARRAYS => [
                'id' => ApiCheck::TYPE_INTEGER,
                'name' => ApiCheck::TYPE_STRING,
            ]
        ]
    ]
], $users);
```

```bash
End time: 0.1269 sec.
```

## Who USED ?!

Example base response
```json
{
  "id": 1,
  "name": "Bob"
}
```
ApiCheck
```php
$result = ApiCheck::checker($response, [
    'id' => ApiCheck::TYPE_INTEGER,
    'name' => ApiCheck::TYPE_STRING,
]);
```

Example for array
```json
{
  "data": [
    {
       "name": ["car", "foot", "ball"]
    },
    {
       "name": ["room", "tree"]
    }
  ]
}
```
ApiCheck
```php
$result = ApiCheck::checker($response, [
    'data' => [
        'names' => [
            ApiCheck::TYPE_STRING,
        ],
    ],
]);
```
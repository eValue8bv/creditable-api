# Creditable API PHP Package

[![Latest Stable Version](https://poser.pugx.org/evalue8bv/creditable-api/v)](https://packagist.org/packages/evalue8bv/creditable-api)
[![Total Downloads](https://poser.pugx.org/evalue8bv/creditable-api/downloads)](https://packagist.org/packages/evalue8bv/creditable-api)
[![License](https://poser.pugx.org/evalue8bv/creditable-api/license)](https://packagist.org/packages/evalue8bv/creditable-api)

The Creditable API PHP package is a simple and easy-to-use wrapper for the Creditable API. This package allows you to integrate Creditable PayWall functionalities into your PHP projects.

## Installation

To install the package, use [Composer](https://getcomposer.org/) by running the following command:

```sh
composer require evalue8bv/creditable-api
```

## Usage

```php
<?php

require_once 'vendor/autoload.php';

use Creditable\CreditablePayWall;

$apiKey = 'your-api-key-here';
$creditable = new CreditablePayWall($apiKey);

// Example usage
$data = [
...
];

try {
    $response = $creditable->check($data);

    if ($response->isPaid()) {
        // Access granted
    } else {
        // Access denied
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

```

## API Documentation

For more information about the Creditable API, please refer to the [official documentation](https://www.creditable.news/en/integration-manual).

## Contributing

If you would like to contribute, please feel free to submit a pull request on our [GitHub repository](https://github.com/eValue8bv/creditable-api).

## License

This package is released under the [MIT License](https://opensource.org/licenses/MIT).

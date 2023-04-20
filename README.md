# Creditable PayWall PHP Package

[![Latest Stable Version](https://poser.pugx.org/evalue8bv/creditable-api/v)](https://packagist.org/packages/evalue8bv/creditable-api)
[![Total Downloads](https://poser.pugx.org/evalue8bv/creditable-api/downloads)](https://packagist.org/packages/evalue8bv/creditable-api)
[![License](https://poser.pugx.org/evalue8bv/creditable-api/license)](https://packagist.org/packages/evalue8bv/creditable-api)

The Creditable API PHP package is a simple and easy-to-use wrapper for the Creditable API. This package allows you to integrate Creditable PayWall functionalities into your PHP projects.

## Requirements

To use the Creditable pay per article button, the following things are required:

* Get yourself a free Creditable Partner Account at https://partner.creditable.news. No signup costs are applicable.
* Login to your dashboard at https://partner.creditable.news and add your media title(s).
* An apikey will be generated for each media title you add.

## Installation

To install the package, use [Composer](https://getcomposer.org/) by running the following command:

```sh
composer require evalue8bv/creditable-paywall
```

## Usage

The javascript code of Creditable requires you to have the following HTML elements on your page:

```html
<div id="creditable-container" class="creditable-container">
    <!-- the button -->
    <div id="creditable-button"></div>
    <!-- popup window -->
    <div id="creditable-window"></div>
</div>
```
Include the following stylesheet in your head element:
```html
<link rel="stylesheet" type="text/css" href="<?= $creditable->getCssDependancy(); ?>;" />
```

### Checking for article access
```php
<?php

require_once 'vendor/autoload.php';

use Creditable\CreditablePayWall;

$apiKey = 'your-api-key-here';
$creditable = new CreditablePayWall($apiKey);

$creditable_article_id = "<<ARTICLE ID>>"; // Alphanumeric (required)
$creditable_article_title = "<<ARTICLE TITLE>>"; // Alphanumeric (required)
$creditable_topic_id = "<<TOPIC ID>>"; // Alphanumeric (required)
$creditable_topic_name = "<<TOPIC NAME>>"; // Alphanumeric (required)
$creditable_article_url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; // Alphanumeric (required)
$creditable_article_lang = "nl-NL"; // ISO (required)
$creditable_article_author = "<<AUTHOR>>"; // Alphanumeric (optional)
$creditable_article_desc = "<<ARTICLE DESC>>"; // Alphanumeric (optional) teaser, used to tease recommended articles to users)
$creditable_article_tags = "<<TAGS>>"; // Alphanumeric (optional) comma delimited list or json (optional keywords, used to find recommended articles for users)
$creditable_article_img = "<<ARTICLE IMG URL>>"; // Alphanumeric (optional) URL for article image

// SET DEFAULT STATE
$creditable_paid = false;

// GET LOCAL CREDITABLE JWT COOKIE
$creditable_cookie = $_COOKIE['cjwt'] ?? "";

// Example usage
$data = [
    'jwt' => $creditable_cookie,
    'article_id' => $creditable_article_id,
    'article_name' => $creditable_article_title,
    'topic_id' => $creditable_topic_id,
    'topic_name' => $creditable_topic_name,
    'article_url' => $creditable_article_url,
    'article_lang' => $creditable_article_lang,
    'article_author' => $creditable_article_author,
    'article_desc' => $creditable_article_desc, 
    'article_tags' => $creditable_article_tags,
    'article_img' => $creditable_article_img
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
### JavaScript include tag:
```php
<!-- creditable scripts -->
<?php if (!$creditable_paid){ ?>
<script src="<?= $creditable->getJsDependancy(); ?>" type="text/javascript">
    <!--//
    var cUid = <?= $creditable->uid; ?>;
    //-->
</script>
<?php } ?>
```

## API Documentation

For more information about the Creditable API, please refer to the [official documentation](https://www.creditable.news/en/integration-manual).

## Contributing

If you would like to contribute, please feel free to submit a pull request on our [GitHub repository](https://github.com/eValue8bv/creditable-api).

## License

This package is released under the [MIT License](https://opensource.org/licenses/MIT).

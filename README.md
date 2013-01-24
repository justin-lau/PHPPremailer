# PHPPremailer - PHP wrapper classes for [Premailer API](http://premailer.dialect.ca/api)

PHPPremailer is a simple object oriented library that wraps up the API calls to and respond from
the [Premailer API 0.1](http://premailer.dialect.ca/api). It follows the original API naming as
close as possible and it should be easy to adapt.

## Change Log

* 0.1.0 (2013-01-25)

	* Initiate commit.

## Requirements

PHPPremailer requires PHP 5.3 for the namespace.

[Client URL Library](http://php.net/manual/en/book.curl.php) of PHP is also needed for sending HTTP requests.

## Example

### Use a URL as the source

```php
<?php

use PHPPremailer\PremailerClient;

// pass a class constant or simply 'url' to define source type
$client = new PremailerClient(PremailerClient::SOURCE_TYPE_URL, 'http://dialect.ca/premailer-tests/base.html');

// fluent method call
$response = $client->send()->get_response();

// get html result
$html_result = $response->get_html();

// get plain text result
$text_result = $response->get_text();
```

### Exception

The follow code generates exception due to no URL or HTML is set:

```php
$client = new PremailerClient();

$response = $client->send(); // generates PHPPremailer\PremailerException !
```

## Testing

PHPPremailer has a 100% coverage test class included. In order to run the test, you must have
[PHPUnit](phpunit --stderr --bootstrap tests/bootstrap.php tests/tests.php) installed.

### Running Test

```bash
cd tests
phpunit PremailerTest
```
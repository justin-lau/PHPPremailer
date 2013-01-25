# PHPPremailer - PHP wrapper classes for [Premailer API](http://premailer.dialect.ca/api)

PHPPremailer is a simple object oriented library that wraps up the API calls to and respond from
the [Premailer API 0.1](http://premailer.dialect.ca/api). It follows the original API naming as
closely as possible and it should be easy to adapt.

## Change Log

0.1.1 (2013-01-25)
	
 - Added CA Cert Suppport

0.1.0 (2013-01-25)

 - Initial commit.

## Requirements

* PHP 5.3

* [Client URL Library](http://php.net/manual/en/book.curl.php)

## Examples

### Use a URL as the source

```php
<?php

use PHPPremailer\PremailerClient;

// pass a class constant to define source type
$client = new PremailerClient(PremailerClient::SOURCE_TYPE_URL, 'http://dialect.ca/premailer-tests/base.html');

// or use a literal string
$client = new PremailerClient('url', 'http://dialect.ca/premailer-tests/base.html');

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

## cURL SSL Connection

Although the Premailer API do not support secure connection, both the processed HTML and plain text
are stored on Amazon S3 storage service that offer HTTPS.

If you encountered an `PHPPremailer\PremailerException` with the following message, it means your
cURL library wasn't able to find a valid CA Certificate to verify the HTTPS connection:

```
cURL Error[60]: SSL certificate problem, verify that the CA cert is OK.
```

According to [cURL's documentation](http://curl.haxx.se/docs/sslcerts.html), you can either disabled
the verification, or let cURL be aware of a CA Certificate that can verify the connection.
PHPPremailer is flexible and let you do either.

Disabling the verification makes cURL vulnerable to [Man-in-the-middle](http://en.wikipedia.org/wiki/Man-in-the-middle_attack)
attacks, but for trivial or testing purpose you can do so:

```php
// pass a class constant
$html_result = $response->get_html(PremailerClient::SSL_NOT_VERIFY);
// or
$html_result = $response->get_html('not verify');
```

To use a CA Certificate Bundle, just place the pem file at the root folder of the library and name
it `cacert.pem`. The file can be created in many way, and the easiest one among them is to
[download](http://curl.haxx.se/docs/caextract.html) one from cURL.

## Testing

PHPPremailer has a 100% coverage test class included. In order to run the test, you must have
[PHPUnit](phpunit --stderr --bootstrap tests/bootstrap.php tests/tests.php) installed.

### Running Test

```bash
cd tests
phpunit PremailerTest
```
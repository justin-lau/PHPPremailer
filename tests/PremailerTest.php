<?php
/**
 * @author      Justin Lau
 * @copyright   Copyright (c) 2013 Justin Lau <justin@tclau.com>
 * @version     0.1
 * @license     MIT
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed
 * with this package.
 */

namespace PHPPremailer\Test; 

require_once '../src/PremailerClient.php';

use \PHPUnit_Framework_TestCase;
use PHPPremailer\PremailerClient;
use PHPPremailer\PremailerResponse;

class PremailerTest extends PHPUnit_Framework_TestCase {
	
	const PREMAILER_TEST_URL = 'http://dialect.ca/premailer-tests/base.html';
	const URL_HTML_FILE      = 'data/url.html';
	const URL_TEXT_FILE      = 'data/url.txt';
	const ORIGINAL_FILE      = 'data/original.html';
	const HTML_HTML_FILE     = 'data/html.html';
	const HTML_TEXT_FILE     = 'data/html.txt';
	
	/**
	 * Original html file
	 *
	 * @var string
	 */
	private $original_file;	
	
	/**
	 * Read original html file
	 *
	 * @return string
	 */
	private function get_original_file()
	{
		if($this->original_file === null)
			$this->original_file = file_get_contents(self::ORIGINAL_FILE);
		
		return $this->original_file;
	}
	
	public function test_basics()
	{
		$client = new PremailerClient();
		
		$this->assertEquals(null, $client->get_source_type());
		$this->assertEquals(null, $client->get_source_text());
		$this->assertEquals(PremailerClient::ADAPTER_HPRICOT, $client->get_adapter());
		$this->assertEquals('', $client->get_base_url());
		$this->assertEquals(PremailerClient::DEFAULT_LINE_LENGTH, $client->get_line_length());
		$this->assertEquals('', $client->get_link_query_string());
		$this->assertEquals(PremailerClient::DEFAULT_PRESERVE_STYLES, $client->get_preserve_styles());
		$this->assertEquals(PremailerClient::DEFAULT_REMOVE_IDS, $client->get_remove_ids());
		$this->assertEquals(PremailerClient::DEFAULT_REMOVE_CLASSES, $client->get_remove_classes());
		$this->assertEquals(PremailerClient::DEFAULT_REMOVE_COMMENTS, $client->get_remove_comments());
	}
	
	public function test_source()
	{
		$url = 'http://www.google.com';
		$html = '';
		$client = new PremailerClient();
		
		$client->set_source(null, $url);
		$this->assertEquals(null, $client->get_source_type());
		$this->assertEquals(null, $client->get_source_text());
		
		$client->set_source(PremailerClient::SOURCE_TYPE_URL, null);
		$this->assertEquals(null, $client->get_source_type());
		$this->assertEquals(null, $client->get_source_text());
		
		$client->set_source(null, null);
		$this->assertEquals(null, $client->get_source_type());
		$this->assertEquals(null, $client->get_source_text());
	
		$client->set_source(PremailerClient::SOURCE_TYPE_URL, $url);
		$this->assertEquals(PremailerClient::SOURCE_TYPE_URL, $client->get_source_type());
		$this->assertEquals($url, $client->get_source_text());
		
		$client->set_source(PremailerClient::SOURCE_TYPE_HTML, $this->get_original_file());
		$this->assertEquals(PremailerClient::SOURCE_TYPE_HTML, $client->get_source_type());
		$this->assertEquals($this->get_original_file(), $client->get_source_text());
	}
	
	/**
     * @expectedException        PHPPremailer\PremailerException
	 * @expectedExceptionMessage source_type must be one of Premailer::SOURCE_TYPE_URL or Premailer::SOURCE_TYPE_HTML
     */
	public function test_set_source_exception()
	{
		$client = new PremailerClient();
	
		$client->set_source('foo', 'bar');
	}
	
	public function test_adapter()
	{
		$client = new PremailerClient();
		
		$client->set_adapter();
		$this->assertEquals(PremailerClient::ADAPTER_HPRICOT, $client->get_adapter());
		
		$client->set_adapter(PremailerClient::ADAPTER_HPRICOT);
		$this->assertEquals(PremailerClient::ADAPTER_HPRICOT, $client->get_adapter());
		
		$client->set_adapter(PremailerClient::ADAPTER_NOKOGIRI);
		$this->assertEquals(PremailerClient::ADAPTER_NOKOGIRI, $client->get_adapter());
	}
	
	/**
     * @expectedException        PHPPremailer\PremailerException
	 * @expectedExceptionMessage $adapter must be one of Premailer::ADAPTER_HPRICOT or Premailer::ADAPTER_NOKOGIRI
     */
	public function test_set_adapter_exception()
	{
		$client = new PremailerClient();
	
		$client->set_adapter('foo');
	}
	
	public function test_send_html()
	{
		$client = new PremailerClient(PremailerClient::SOURCE_TYPE_HTML, $this->get_original_file());
		
		$response = $client->send()->get_response();
		$alt_response = new PremailerResponse($client->get_response_text());
		
		$this->assertEquals(PremailerResponse::STATUS_SUCCESS, $response->get_status());
		
		$this->assertEquals('Created', $response->get_message());
		$this->assertEquals('0.1', $response->get_version());
		
		$this->assertStringEqualsFile(self::HTML_HTML_FILE, $response->get_html(PremailerResponse::SSL_NOT_VERIFY));
		$this->assertStringEqualsFile(self::HTML_TEXT_FILE, $response->get_text(PremailerResponse::SSL_NOT_VERIFY));
		
		$options = $response->get_options();
		
		$this->assertEquals(PremailerClient::ADAPTER_HPRICOT, $options['adapter']);
		$this->assertEquals(false, isset($options['base_url']));
		$this->assertEquals((string) PremailerClient::DEFAULT_LINE_LENGTH, $options['line_length']);
		$this->assertEquals('', $options['link_query_string']);
		$this->assertEquals(PremailerClient::DEFAULT_PRESERVE_STYLES, $options['preserve_styles']);
		$this->assertEquals(PremailerClient::DEFAULT_REMOVE_IDS, $options['remove_ids']);
		$this->assertEquals(PremailerClient::DEFAULT_REMOVE_CLASSES, $options['remove_classes']);
		$this->assertEquals(PremailerClient::DEFAULT_REMOVE_COMMENTS, $options['remove_comments']);
	}
	
	public function test_send_url()
	{
		$client = new PremailerClient(PremailerClient::SOURCE_TYPE_URL, self::PREMAILER_TEST_URL);
		
		$response = $client->send()->get_response();
		$alt_response = new PremailerResponse($client->get_response_text());
		
		$this->assertEquals(PremailerResponse::STATUS_SUCCESS, $response->get_status());
		
		$this->assertEquals('Created', $response->get_message());
		$this->assertEquals('0.1', $response->get_version());
		
		$this->assertStringEqualsFile(self::URL_HTML_FILE, $response->get_html(PremailerResponse::SSL_NOT_VERIFY));
		$this->assertStringEqualsFile(self::URL_TEXT_FILE, $response->get_text(PremailerResponse::SSL_NOT_VERIFY));
		
		$options = $response->get_options();
		
		$this->assertEquals(PremailerClient::ADAPTER_HPRICOT, $options['adapter']);
		$this->assertEquals(false, isset($options['base_url']));
		$this->assertEquals((string) PremailerClient::DEFAULT_LINE_LENGTH, $options['line_length']);
		$this->assertEquals('', $options['link_query_string']);
		$this->assertEquals(PremailerClient::DEFAULT_PRESERVE_STYLES, $options['preserve_styles']);
		$this->assertEquals(PremailerClient::DEFAULT_REMOVE_IDS, $options['remove_ids']);
		$this->assertEquals(PremailerClient::DEFAULT_REMOVE_CLASSES, $options['remove_classes']);
		$this->assertEquals(PremailerClient::DEFAULT_REMOVE_COMMENTS, $options['remove_comments']);
	}
	
	/**
     * @expectedException        PHPPremailer\PremailerException
	 * @expectedExceptionMessage Either the url or html must be provided
     */
	public function test_send_exception()
	{
		$client = new PremailerClient();
	
		$client->send();
	}
}
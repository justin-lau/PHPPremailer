<?php
/**
 * PHPPremailer\PremailerClient
 *
 * @author      Justin Lau
 * @copyright   Copyright (c) 2013 Justin Lau <justin@tclau.com>
 * @version     0.1
 * @license     MIT
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed
 * with this package.
 */

namespace PHPPremailer;

require_once 'PremailerException.php';
require_once 'PremailerResponse.php';

/**
 * This class wraps up the API calls to Premailer.
 */
class PremailerClient {

	const ADAPTER_HPRICOT         = 'hpricot';
	const ADAPTER_NOKOGIRI        = 'nokogiri';
	const DEFAULT_LINE_LENGTH     = 65;
	const DEFAULT_PRESERVE_STYLES = true;
	const DEFAULT_REMOVE_IDS      = false;
	const DEFAULT_REMOVE_CLASSES  = false;
	const DEFAULT_REMOVE_COMMENTS = false;
	const PREMAILER_URL           = 'http://premailer.dialect.ca/api/0.1/documents';
	const SOURCE_TYPE_URL         = 'url';
	const SOURCE_TYPE_HTML        = 'html';

	/**
	 * Source file type: SOURCE_TYPE_URL or SOURCE_TYPE_HTML
	 *
	 * @var string
	 */	
	protected $source_type;
	
	/**
	 * If $source_type is url this is the url, if $source_type if html this is the html
	 * body
	 *
	 * @var string
	 */	
	protected $source_text;
	
	/**
	 * Which document handler to use
	 *
	 * @var string
	 */	
	protected $adapter;
	
	/**
	 * Base URL for converting relative links
	 *
	 * @var string
	 */	
	protected $base_url;
	
	/**
	 * Length of lines in the plain text version
	 *
	 * @var int
	 */	
	protected $line_length;
	
	/**
	 * Query string appended to links
	 *
	 * @var string
	 */	
	protected $link_query_string;
	
	/**
	 * Whether to preserver any /link rel="stylesheet"/ and style elements
	 *
	 * @var bool
	 */	
	protected $preserve_styles;
	
	/**
	 * Remove IDs from the HTML element?
	 *
	 * @var bool
	 */	
	protected $remove_ids;
	
	/**
	 * Remove classes from the HTML element?
	 *
	 * @var bool
	 */	
	protected $remove_classes;
	
	/**
	 * Remove comments from the HTML element?
	 *
	 * @var bool
	 */	
	protected $remove_comments;
	
	/**
	 * Response object
	 *
	 * @var PremailerResponse
	 */	
	protected $response;
	
	/**
	 * Response retrieved from Premailer API
	 *
	 * @var string
	 */	
	protected $response_text;
	
	/**
	 * Create a new Premailer instance.
	 *
	 * @param  string  $source_type
	 * @param  string  $source_text
	 * @param  string  $adapter
	 * @param  string  $base_url
	 * @param  int     $line_length
	 * @param  string  $link_query_string
	 * @param  bool    $preserve_styles
	 * @param  bool    $remove_ids
	 * @param  bool    $remove_classes
	 * @param  bool    $remove_comments
	 * @return self
	 */
	public function __construct($source_type       = null,
								$source_text       = null,
								$adapter           = 'hpricot',
								$base_url          = '',
								$line_length       = self::DEFAULT_LINE_LENGTH,
								$link_query_string = '',
								$preserve_styles   = self::DEFAULT_PRESERVE_STYLES,
								$remove_ids        = self::DEFAULT_REMOVE_IDS,
								$remove_classes    = self::DEFAULT_REMOVE_CLASSES,
								$remove_comments   = self::DEFAULT_REMOVE_COMMENTS)
	{
		$this->set_source($source_type, $source_text);
		$this->set_adapter($adapter);		
		$this->set_base_url($base_url);
		$this->set_line_length($line_length);
		$this->set_link_query_string($link_query_string);
		$this->set_preserve_styles($preserve_styles);
		$this->set_remove_ids($remove_ids);
		$this->set_remove_classes($remove_classes);
		$this->set_remove_comments($remove_comments);
		$this->respoonse_text = null;
		$this->respoonse_json = null;
	}
	
	/**
	 * Get source type.
	 *
	 * @return string|null
	 */
	public function get_source_type()
	{
		return $this->source_type;
	}
	
	/**
	 * Get source text.
	 *
	 * @return string|null
	 */
	public function get_source_text()
	{
		return $this->source_text;
	}
	
	/**
	 * Set source. Passing null as either parameter sets both to null.
	 *
	 * @param  string $source_type
	 * @param  string $source_text
	 * @return self
	 */
	public function set_source($source_type, $source_text)
	{
		if($source_type === null || $source_text === null)
		{
			$this->source_type = null;
			$this->source_text = null;
		}
		else
		{		
			switch($source_type)
			{
				case self::SOURCE_TYPE_URL:
				case self::SOURCE_TYPE_HTML:
					$this->source_type = (string) $source_type;
					$this->source_text = (string) $source_text;
					break;
				default:
					throw new PremailerException('source_type must be one of Premailer::SOURCE_TYPE_URL or Premailer::SOURCE_TYPE_HTML');
			}
		}
		
		return $this;
	}
	
	/**
	 * Get document handler.
	 *
	 * @return string
	 */
	public function get_adapter()
	{
		return $this->adapter;
	}
	
	/**
	 * Set document handler.
	 *
	 * @param  string $adapter
	 * @return self
	 */
	public function set_adapter($adapter = self::ADAPTER_HPRICOT)
	{
		switch($adapter)
		{
			case self::ADAPTER_HPRICOT:
			case self::ADAPTER_NOKOGIRI:
			case null:
				$this->adapter = $adapter;
				break;
			default:
				throw new PremailerException('$adapter must be one of Premailer::ADAPTER_HPRICOT or Premailer::ADAPTER_NOKOGIRI');
		}
		
		return $this;
	}
	
	/**
	 * Get base url.
	 *
	 * @return string
	 */
	public function get_base_url()
	{
		return $this->base_url;
	}
	
	/**
	 * Set base url.
	 *
	 * @param  string $base_url
	 * @return self
	 */
	public function set_base_url($base_url = '')
	{
		$this->base_url = (string) $base_url;
	}
	
	/**
	 * Get line length.
	 *
	 * @return int
	 */
	public function get_line_length()
	{
		return $this->line_length;
	}
	
	/**
	 * Set line length.
	 *
	 * @param  int  $line_length
	 * @return self
	 */
	public function set_line_length($line_length = self::DEFAULT_LINE_LENGTH)
	{
		$this->line_length = (int) $line_length;
	}
	
	/**
	 * Get query string appended to links.
	 *
	 * @return string
	 */
	public function get_link_query_string()
	{
		return $this->link_query_string;
	}
	
	/**
	 * Set query string appended to links.
	 *
	 * @param  string $link_query_string
	 * @return self
	 */
	public function set_link_query_string($link_query_string = '')
	{
		$this->link_query_string = (string) $link_query_string;
	}
	
	/**
	 * Get whether to preserver styles.
	 *
	 * @return bool
	 */
	public function get_preserve_styles()
	{
		return $this->preserve_styles;
	}
	
	/**
	 * Set whether to preserver styles.
	 *
	 * @param  bool $preserve_styles
	 * @return self
	 */
	public function set_preserve_styles($preserve_styles = self::DEFAULT_PRESERVE_STYLES)
	{
		$this->preserve_styles = (bool) $preserve_styles;
	}
	
	/**
	 * Get whether to remove IDs from the HTML document.
	 *
	 * @return bool
	 */
	public function get_remove_ids()
	{
		return $this->remove_ids;
	}
	
	/**
	 * Set whether to remove IDs from the HTML document.
	 *
	 * @param  bool $remove_ids
	 * @return self
	 */
	public function set_remove_ids($remove_ids = self::DEFAULT_REMOVE_IDS)
	{
		$this->remove_ids = (bool) $remove_ids;
	}
	
	/**
	 * Get whether to remove classes from the HTML document.
	 *
	 * @return bool
	 */
	public function get_remove_classes()
	{
		return $this->remove_classes;
	}
	
	/**
	 * Set whether to remove classes from the HTML document.
	 *
	 * @param  bool $remove_classes
	 * @return self
	 */
	public function set_remove_classes($remove_classes = self::DEFAULT_REMOVE_CLASSES)
	{
		$this->remove_classes = (bool) $remove_classes;
	}
	
	/**
	 * Get whether to remove comments from the HTML document.
	 *
	 * @return bool
	 */
	public function get_remove_comments()
	{
		return $this->remove_comments;
	}
	
	/**
	 * Set whether to remove comments from the HTML document.
	 *
	 * @param  bool $remove_comments
	 * @return self
	 */
	public function set_remove_comments($remove_comments = self::DEFAULT_REMOVE_COMMENTS)
	{
		$this->remove_comments = (bool) $remove_comments;
	}
	
	/**
	 * Get response object.
	 *
	 * @return PremailerResponse|null
	 */
	public function get_response()
	{
		return $this->response;
	}
	
	/**
	 * Get response text.
	 *
	 * @return string|null
	 */
	public function get_response_text()
	{
		return $this->response_text;
	}
	
	/**
	 * Send request to Premailer API
	 *
	 * @return self
	 */
	public function send()
	{
		if($this->source_type === null || $this->source_text === null)
			throw new PremailerException('Either the url or html must be provided');
		
		// Post data
		$fields = array(
			$this->source_type  => urlencode($this->source_text),
			'adapter'           => $this->adapter,
			'line_length'       => (string) $this->line_length,
			'link_query_string' => urlencode($this->link_query_string),
			'preserve_styles'   => $this->preserve_styles ? 'true' : 'false',
			'remove_ids'        => $this->remove_ids ? 'true' : 'false',
			'remove_classes'    => $this->remove_classes ? 'true' : 'false',
			'remove_comments'   => $this->remove_comments ? 'true' : 'false',
		);
		
		// As of Premailer 0.1, if an empty base_url is supplied, relative links all become empty
		if(strlen($this->base_url) > 0)
			$fields['base_url'] = urlencode($this->base_url);
		
		$fields_string = '';
		
		foreach($fields as $key => $value)
			$fields_string .= $key . '=' . $value . '&';
			
		rtrim($fields_string, '&');
		
		//open connection
		$curl_resource = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt_array($curl_resource, array(
			CURLOPT_URL            => self::PREMAILER_URL,
			CURLOPT_POST           => count($fields),
			CURLOPT_POSTFIELDS     => $fields_string,
			CURLOPT_RETURNTRANSFER => true,
		));

		//execute post
		$response_text = curl_exec($curl_resource);
		
		$errno = curl_errno($curl_resource);
		$error = curl_error($curl_resource);

		//close connection
		curl_close($curl_resource);
		
		if($errno != 0)
			throw new PremailerException(sprintf('cURL Error[%d]: %s', $errno, $error), $errno);
		
		$this->response_text = $response_text;
		$this->response = new PremailerResponse($response_text);
		
		return $this;
	}
}
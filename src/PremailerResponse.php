<?php
/**
 * PHPPremailer\PremailerResponse
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

/**
 * This class wraps up the respond from API calls to Premailer.
 */
class PremailerResponse {
	
	const STATUS_SUCCESS             = 201;
	const STATUS_MISSING_SOURCE_FILE = 400;
	const STATUS_ERROR               = 500;
	const SSL_VERIFY                 = 'verify';
	const SSL_NOT_VERIFY             = 'not_verify';
	
	/**
	 * Response code
	 *
	 * @var int
	 */	
	protected $status;
	
	/**
	 * Response message
	 *
	 * @var string
	 */	
	protected $message;
	
	/**
	 * The API version
	 *
	 * @var string
	 */	
	protected $version;
	
	/**
	 * URLs to processed results
	 *
	 * @var array
	 */	
	protected $documents;
	
	/**
	 * Echoed configuration options used by Premailer for the request
	 *
	 * @var array
	 */	
	protected $options;
	
	/**
	 * Create a new PremailerReponse instance.
	 *
	 * @param  string  $response_text
	 * @return self
	 */
	public function __construct($response_text)
	{
		$data = json_decode($response_text);
		
		$this->status = (int) $data->status;
		$this->message = $data->message;
		$this->version = $data->version;
		$this->documents = (array) $data->documents;
		$this->options = (array) $data->options;
	}
	
	/**
	 * Get reponse status.
	 *
	 * @return int
	 */
	public function get_status()
	{
		return $this->status;
	}
	
	/**
	 * Get reponse message.
	 *
	 * @return string
	 */
	public function get_message()
	{
		return $this->message;
	}
	
	/**
	 * Get API version.
	 *
	 * @return string
	 */
	public function get_version()
	{
		return $this->version;
	}
	
	/**
	 * Get processed html results.
	 *
	 * @param  string $ssl_mode
	 * @return string
	 */
	public function get_html($ssl_mode = self::SSL_VERIFY)
	{
		return self::fetch_url($this->documents['html'], $ssl_mode);
	}
	
	/**
	 * Get processed plain text results.
	 *
	 * @param  string $ssl_mode
	 * @return string
	 */
	public function get_text($ssl_mode = self::SSL_VERIFY)
	{
		return self::fetch_url($this->documents['txt'], $ssl_mode);
	}
	
	/**
	 * Get configuration options.
	 *
	 * @return array
	 */
	public function get_options()
	{
		return $this->options;
	}
	
	/**
	 * Fetch url with curl.
	 *
	 * @param  string $url
	 * @param  string $ssl_mode
	 * @return string
	 */
	protected static function fetch_url($url, $ssl_mode = self::SSL_VERIFY)
	{
		if($ssl_mode != self::SSL_VERIFY && $ssl_mode != self::SSL_NOT_VERIFY)
			throw new PremailerException('$ssl_mode must be one of PremailerResponse::SSL_VERIFY or PremailerResponse::SSL_NOT_VERIFY');
	
		//open connection
		$curl_resource = curl_init();
	
		//set the url, number of POST vars, POST data
		curl_setopt_array($curl_resource, array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => $ssl_mode == self::SSL_VERIFY,
		));

		//execute post
		$response_text = curl_exec($curl_resource);
		
		$errno = curl_errno($curl_resource);
		$error = curl_error($curl_resource);

		//close connection
		curl_close($curl_resource);
		
		if($errno != 0)
			throw new PremailerException(sprintf('cURL Error[%d]: %s', $errno, $error), $errno);
		
		return $response_text;
	}
}

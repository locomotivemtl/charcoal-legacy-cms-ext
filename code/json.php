<?php

/**
 * JSON Helper
 *
 * @package   Locomotive\Utilities
 *
 * @copyright © Locomotive 2014
 * @author    Chauncey McAskill <chauncey@locomotive.ca>
 * @version   2015-09-01
 * @since     Version 2014-10-02
 */
class JSON {

	/** @link http://json.org */
	const SPEC = '
	/
	(?(DEFINE)
		(?<number>   -? (?= [1-9]|0(?!\d) ) \d+ (\.\d+)? ([eE] [+-]? \d+)? )
		(?<boolean>   true | false | null )
		(?<string>    " ([^"\\\\]* | \\\\ ["\\\\bfnrt\/] | \\\\ u [0-9a-f]{4} )* " )
		(?<array>     \[  (?:  (?&json)  (?: , (?&json)  )*  )?  \s* \] )
		(?<pair>      \s* (?&string) \s* : (?&json)  )
		(?<object>    \{  (?:  (?&pair)  (?: , (?&pair)  )*  )?  \s* \} )
		(?<json>   \s* (?: (?&number) | (?&boolean) | (?&string) | (?&array) | (?&object) ) \s* )
	)
	\A (?&json) \Z
	/six';

	/** @link http://tools.ietf.org/html/rfc4627 */
	const RFC4627 = '/[^,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t]/';

	/**
	 * Alias of {@see json_encode()}
	 */
	public static function encode()
	{
		return call_user_func_array( 'json_encode', func_get_args() );
	}

	/**
	 * Alias of {@see json_decode()}
	 */
	public static function decode()
	{
		return call_user_func_array( 'json_decode', func_get_args() );
	}

	/**
	 * Validate the stringified JSON object.
	 *
	 * @param string      $json  The JSON string being validated.
	 * @param string|null $spec  Optional. The specification to follow for validation.
	 * @param string      $depth Optional. User specified recursion depth. Defaults to 512.
	 *
	 * @return bool
	 */
	public static function is_valid( $json, $spec = null, $depth = 512 )
	{
		switch ( $spec ) {
			case self::SPEC:
				return preg_match( self::SPEC, $json );

			case self::RFC4627:
				$json = preg_replace( '/"(\\.|[^"\\\\])*"/', '', $json );
				return ! preg_match( self::RFC4627, $json );

			default:
				json_decode( $json );
				return ( json_last_error() === JSON_ERROR_NONE );
		}

	}

	/**
	 * Send a JSON response back to an XHR request.
	 *
	 * @param array|object $data Variable (usually an array or object) to encode as JSON.
	 */
	public static function send( $response )
	{
		if ( ! headers_sent() ) {
			header('Content-Type: application/json; charset=UTF-8');
		}

		echo json_encode( $response );

		exit;
	}

	/**
	 * Send a JSON response back to an XHR request, indicating success.
	 *
	 * @param array|object $data Optional. Data to encode as JSON, then print and die.
	 */
	public static function send_success( $data = null )
	{
		$response = [ 'success' => true ];

		if ( isset( $data ) ) {
			$response['data'] = $data;
		}

		self::send( $response );
	}

	/**
	 * Send a JSON response back to an XHR request, indicating failure.
	 *
	 * @param array|object $data Optional. Data to encode as JSON, then print and die.
	 */
	public static function send_error( $data = null )
	{
		$response = [ 'success' => false ];

		if ( isset( $data ) ) {
			$response['data'] = $data;
		}

		self::send( $response );
	}
}

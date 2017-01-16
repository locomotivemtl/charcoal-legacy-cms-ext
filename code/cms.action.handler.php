<?php

/**
 * File: CMS Action Handler Class
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Mathieu Ducharme <mat@locomotive.ca>
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2015-09-30
 */

use \Negotiation\Negotiator;

/**
 * Class: CMS Action Handler
 *
 * The CMS module's {@see Charcoal_Action} controller/handler/helper.
 *
 * @package CMS\Objects
 */
class CMS_Action_Handler
{
	/**
	 * A default key to indentify the response to set/get to the client's session.
	 *
	 * @var string
	 */
	public static $session_key = 'cms.action.response';

	/**
	 * Determine if the request is the result of an AJAX call.
	 *
	 * @var bool
	 */
	protected static $_is_ajax_request;

	/**
	 * Determine if the request is the result of an AJAX call.
	 *
	 * @param bool $define_const Optional. Defines the IS_AJAX constant, if undefined.
	 *
	 * @return bool
	 */
	public static function is_ajax_request( $set_const = false )
	{
		if ( is_null(self::$_is_ajax_request) ) {
			$headers  = [ 'HTTP_X_REQUESTED_WITH', 'X_REQUESTED_WITH', 'X-Requested-With' ];
			$requests = [ 'xmlhttprequest', 'jsonhttprequest' ];

			foreach ( $headers as $header ) {
				$with = getenv($header);

				if ( $with && is_string($with) ) {
					$with = strtolower($with);

					if ( in_array( $with, $requests ) ) {
						$with = (bool) $with;
						break;
					}
				}
			}

			self::$_is_ajax_request = $with;
		}

		if ( $set_const && ! defined('IS_AJAX') ) {
			define( 'IS_AJAX', self::$_is_ajax_request );
		}

		return self::$_is_ajax_request;
	}

	/**
	 * Determine if the response was successful.
	 *
	 * @param string $session_key Optional. Defines the array to extract from the session object.
	 *
	 * @return bool|null Returns NULL if there was no response, otherwise a boolean.
	 */
	public static function is_successful_response( $session_key = null )
	{
		if ( is_null( $session_key ) ) {
			$session_key = self::$session_key;
		}

		if ( self::has_response( $session_key ) ) {
			$module   = Charcoal::obj('CMS_Module');
			$response = &$_SESSION[ $module::$session_key ][ $session_key ];

			if ( isset( $response['success'] ) ) {
				return $response['success'];
			}
			elseif ( isset( $response['feedback'] ) ) {
				return empty( $response['feedback'] );
			}
			else {
				$query_uri = parse_url( getenv('REQUEST_URI'), PHP_URL_QUERY );
				$query_arr = [];

				parse_str( $query_uri, $query_arr );

				if ( isset( $query_arr['success'] ) ) {
					return $query_arr['success'];
				}
				elseif ( isset( $query_arr['feedback'] ) ) {
					return empty( $query_arr['feedback'] );
				}
			}

			return false;
		}

		return null;
	}

	/**
	 * Determine if there's a response in the session object or the URI.
	 *
	 * @param string $session_key Optional. Defines the array to extract from the session object.
	 *
	 * @return bool
	 */
	public static function has_response( $session_key = null )
	{
		if ( is_null( $session_key ) ) {
			$session_key = self::$session_key;
		}

		$module = Charcoal::obj('CMS_Module');

		if ( isset( $_SESSION[ $module::$session_key ][ $session_key ]['success'] ) ) {
			return true;
		}
		else {
			$query_uri = parse_url( getenv('REQUEST_URI'), PHP_URL_QUERY );
			$query_arr = [];

			parse_str( $query_uri, $query_arr );

			if (
				isset( $query_arr['success'] ) &&
				isset( $query_arr['redirect_from'] ) &&
				$query_arr['redirect_from'] === $session_key
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieve the response from the session object or the URI.
	 *
	 * @param string $session_key Optional. Defines the array to extract from the session object.
	 *
	 * @return mixed|null Returns NULL if there was no response, otherwise the response data.
	 */
	public static function get_response( $session_key = null )
	{
		if ( is_null( $session_key ) ) {
			$session_key = self::$session_key;
		}

		if ( self::has_response( $session_key ) ) {
			$module = Charcoal::obj('CMS_Module');
			return $_SESSION[ $module::$session_key ][ $session_key ];
		}

		return false;
	}

	/**
	 * Delete the response from the session object or the URI.
	 *
	 * @param string $session_key Optional. Defines the array to delete from the session object.
	 */
	public static function remove_response( $session_key = null )
	{
		if ( is_null( $session_key ) ) {
			$session_key = self::$session_key;
		}

		if ( self::has_response( $session_key ) ) {
			$module = Charcoal::obj('CMS_Module');
			unset( $_SESSION[ $module::$session_key ][ $session_key ] );
		}

		return false;
	}

	/**
	 * Generate a "nonce" token.
	 *
	 * @return string
	 */
	public static function get_token()
	{
		return Charcoal::token( isset( Charcoal::$config['nonce'] ) ? Charcoal::$config['nonce'] : null );
	}

	/**
	 * Encrypt the provided data as a parcel.
	 *
	 * Usefull for generating small hashs of dynamic data for small AJAX requests.
	 *
	 * @param mixed $data
	 * @param mixed $key  Optional
	 *
	 * @return string
	 */
	public static function encrypt_parcel( $data, $key = null )
	{
		$cipher = new \Charcoal_Cipher('base64');

		if ( is_null( $key ) ) {
			$key = self::get_token();
		}

		if ( is_array( $data ) || is_object( $data ) ) {
			$data = json_encode( $data );
		}

		return $cipher->encrypt( $data, $key );
	}

	/**
	 * Decrypt the provided parcel.
	 *
	 * @param string $hash
	 * @param mixed  $key  Optional
	 *
	 * @return mixed
	 */
	public static function decrypt_parcel( $hash, $key = null, $is_array = true )
	{
		$cipher = new \Charcoal_Cipher('base64');

		if ( is_null( $key ) ) {
			$key = self::get_token();
		}

		$data = $cipher->decrypt( $hash, $key );

		if ( $is_array ) {
			if ( JSON::is_valid( $data ) ) {
				return json_decode( $data, true );
			}

			return false;
		}

		return $data;
	}

	/**
	 * Gets external variables and optionally sanitizes them.
	 *
	 * @param int                          $type        One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV.
	 * @param string|Charcoal_Object       $obj_type    The object from which to map $properties to PHP filter types.
	 * @param string[]|Charcoal_Property[] $properties  An array of Charcoal Properties defining the request parameters to sanitize and return.
	 *
	 * @return mixed[] An array containing the values of the requested variables on success, or FALSE on failure.
	 */
	public static function filter_input_properties( $type, $obj_type, $properties = [], $add_empty = true )
	{
		if ( is_string( $obj_type ) ) {
			$obj_type = Charcoal::obj( $obj_type );
		}

		if ( ! $obj_type instanceof Charcoal_Object ) {
			throw new InvalidArgumentException( 'A valid Charcoal Object is required.');
		}

		if ( ! is_array( $properties ) ) {
			$properties = [ $properties ];
		}

		if ( empty( $properties ) ) {
			$properties = $obj_type->properties();
		}

		foreach ( $properties as $key => $ident ) {
			if ( is_string( $ident ) ) {
				$prop = $obj_type->p( $ident );
			}
			else {
				$prop  = $ident;
				$ident = $key;
			}

			$is_prop   = ( $prop instanceof Charcoal_Property );
			$prop_type = ( $is_prop && $prop->type ? $prop->type : null );

			switch ( $prop_type ) {
				case 'email':
					$args[ $ident ] = FILTER_SANITIZE_EMAIL;
					break;

				case 'url':
					$args[ $ident ] = FILTER_SANITIZE_URL;
					break;

				case 'boolean':
					$args[ $ident ] = FILTER_VALIDATE_BOOLEAN;
					break;

				case 'year':
				case 'month':
				case 'integer':
				case 'number':
					$args[ $ident ] = FILTER_SANITIZE_NUMBER_INT;
					break;

				case 'location_coordinate':
				case 'location_latitude':
				case 'location_longitude':
				case 'float':
					$args[ $ident ] = FILTER_SANITIZE_NUMBER_FLOAT;
					break;

				default:
					$args[ $ident ] = FILTER_SANITIZE_STRING;
					break;
			}

			if ( $is_prop && $prop->multiple() ) {
				$args[ $ident ] = [
					'filter'  => $args[ $ident ],
					'flags'   => FILTER_REQUIRE_ARRAY,
					'options' => []
				];

				if ( method_exists( $prop, 'min' ) && $prop->min() ) {
					$args[ $ident ]['options']['min_range'] = $prop->min();
				}

				if (method_exists( $prop, 'max' ) && $prop->max() ) {
					$args[ $ident ]['options']['max_range'] = $prop->max();
				}
			}
		}

		return filter_input_array( $type, $args );
	}

	/**
	 * Resolve the response of the request (action).
	 *
	 * @param mixed ... {
	 *     Various options to define response sent back to requester.
	 *
	 *     @type bool    $success     Determine the success of the request.
	 *     @type string  $redirect_to A destination to redirect the client to (if this isn't an XHR request).
	 *     @type string  $session_key The response's identifier in the session object.
	 *     @type bool    $use_session Determine if $data is stored in the user session or appended as a query string.
	 *     @type mixed[] $data        Data to be sent back to the client.
	 * }
	 */
	public static function resolve_response()
	{
		$negotiator = new Negotiator;
		$priorities = [ 'application/json', 'text/html', 'application/xhtml+xml', 'application/xml' ];
		$accepted   = preg_replace('/(\*\/\*)(?!;q=)/i', '$1;q=0.8', getenv('HTTP_ACCEPT'));
		$media_type = $negotiator->getBest($accepted, $priorities);

		$default_options = [
			'success'     => true,
			'redirect_to' => ( getenv('HTTP_REFERER') ?: Charcoal::$config['URL'] ),
			'use_session' => false,
			'session_key' => self::$session_key,
			'data'        => []
		];

		$args  = func_get_args();
		$first = reset($args);

		if ( 1 === func_num_args() && is_assoc($first) ) {
			$options = $first;
		}
		else {
			$options = [];

			foreach ( $args as $arg ) {
				switch ( gettype($arg) ) {
					case 'boolean':
						$options['success'] = $arg;
						break;

					case 'string':
						$options['redirect_to'] = $arg;
						break;

					case 'array':
						$options['data'] = $arg;
						break;
				}
			}
		}

		if ( ! empty( $options['response'] ) && empty( $options['data'] ) ) {
			$options['data'] = $options['response'];

			unset( $options['response'] );
		}

		$options = parse_config( $default_options, $options );

		if ( 'application/json' === $media_type->getValue() ) {
			if ( $options['success'] ) {
				JSON::send_success($options['data']);
			}
			else {
				JSON::send_error($options['data']);
			}
		}
		else {
			$options['data']['success'] = $options['success'];

			if ( false === headers_sent() && $options['redirect_to'] ) {
				$url = $options['redirect_to'];

				if ( $options['use_session'] ) {
					$module = Charcoal::obj('CMS_Module');
					$_SESSION[ $module::$session_key ][ $options['session_key'] ] = $options['data'];
				}
				else {
					$options['data']['redirect_from'] = $options['session_key'];

					$url = http_build_url( $options['redirect_to'], [ 'query' => http_build_query( $options['data'] ) ] );
				}

				header( 'Location: ' . $url );
				exit;
			}
		}

		return false;
	}
}

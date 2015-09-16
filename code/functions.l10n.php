<?php

/**
 * Translation Utilities
 *
 * @package     Charcoal/Utilities
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2014-04-17
 */

/**
 * Determine if the $lang is a valid, and active, language.
 *
 * @param string $lang Optional. A language code to test.
 *
 * @return bool
 */
function is_language_valid( $lang = '' )
{
	if ( empty( $lang ) ) {
		$lang = _l();
	}

	return in_array( $lang, Charcoal::langs() );
}

/**
 * Alias of {@see is_language_valid()}
 */
function is_language_active( $lang )
{
	return is_language_valid( $lang );
}

/**
 * Determine if the $lang is a hidden (from the public) language.
 *
 * @param string $lang Optional. A language code to test.
 *
 * @return bool
 */
function is_language_hidden( $lang = '' )
{
	if ( empty( $lang ) ) {
		$lang = _l();
	}

	if ( is_language_valid( $lang ) ) {
		if ( isset( Charcoal::$config['languages'][ $lang ]['hidden'] ) ) {
			return ! Charcoal::$config['languages'][ $lang ]['hidden'];
		}
	}
	else {
		throw new InvalidArgumentException('The provided language is not registered or is inactive.');
	}

	return false;
}

/**
 * Retrieve the $lang's project settings
 *
 * @param string $lang Optional
 *
 * @return mixed[]
 */
function get_language_config( $lang = '' )
{
	if ( empty( $lang ) ) {
		$lang = _l();
	}

	if ( is_language_valid( $lang ) ) {
		return Charcoal::$config['languages'][ $lang ];
	}
	else {
		throw new InvalidArgumentException('The provided language is not registered or is inactive.');
	}

	return false;
}

/**
 * Retrieve all languages except current one
 *
 * @param string $skip      A valid language from which to compute alternates.
 * @param bool   $bilingual Whether to return a string or an array if there's only one opposite language.
 *
 * @return string|string[] An array of languages. In a bilingual context, a string.
 */
function alternate_languages( $skip = '', $bilingual = false )
{
	if ( empty( $skip ) ) {
		$skip = _l();
	}
	elseif ( ! is_language_valid( $skip ) ) {
		throw new InvalidArgumentException('The provided language is not registered or is inactive.');

		return [];
	}

	$alternates = [];

	foreach ( Charcoal::langs() as $code ) {
		if ( $skip === $code || is_language_hidden($code) ) {
			continue;
		}

		$alternates[] = $code;
	}

	if ( $bilingual && 1 === count( $alternates ) ) {
		return reset( $alternates );
	}
	else {
		return $alternates;
	}
}

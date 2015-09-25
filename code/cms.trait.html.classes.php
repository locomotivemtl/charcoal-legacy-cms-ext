<?php

/**
 * File: HTML Document Class Attributes Trait
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2015-05-04
 */

namespace CMS;

use \Charcoal as Charcoal;

/**
 * Trait: HTML Document Class Attributes
 *
 * @package CMS\Objects
 */
trait Trait_HTML_Classes
{
	/**
	 * HTML Class Attribute Getter
	 *
	 * Retrieve a class attribute of a specified element.
	 *
	 * @return Closure[] {
	 *     Returns an associative array of Closures to target class attribute rendering methods.
	 *
	 *     @type string                $classes Optional. Additional CSS class names to output.
	 *     @type Mustache_LambdaHelper $lambda  An instance of the Mustache LambdaHelper object.
	 *
	 *     @return string An HTML class attribute for the specified element.
	 * }
	 */
	public function html_class()
	{
		$attr = [
			'root' => function ( $classes = '' ) {
				return $this->root_class( $classes );
			},
			'body' => function ( $classes = '' ) {
				return $this->body_class( $classes );
			}
		];

		return $attr;
	}

	/**
	 * Display the classes for the root element.
	 *
	 * @param string|string[] $classes One or more classes to add to the class list.
	 *
	 * @return string An HTML class attribute
	 */
	public function root_class( $class = '' )
	{
		$classes = $this->get_root_class( $class );

		if ( count( $classes ) ) {
			return ' class="' . htmlspecialchars( implode( ' ', $classes ), ENT_QUOTES ) . '"';
		}

		return '';
	}

	/**
	 * Retrieve the classes for the root element as an array.
	 *
	 * @param string|string[] $classes One or more classes to add to the class list.
	 *
	 * @return string[] $classes Array of classes.
	 */
	protected function get_root_class( $class = '' )
	{
		$classes = [];

		$classes[] = 'has-no-js';

		return $this->filter_html_class( $classes, $class );
	}

	/**
	 * Display the classes for the body element.
	 *
	 * @param string|string[] $classes One or more classes to add to the class list.
	 *
	 * @return string An HTML class attribute
	 */
	public function body_class( $class = '' )
	{
		$classes = $this->get_body_class( $class );
		if ( count( $classes ) ) {
			return ' class="' . htmlspecialchars( implode( ' ', $classes ), ENT_QUOTES ) . '"';
		}
		return '';
	}

	/**
	 * Retrieve the classes for the body element as an array.
	 *
	 * @param string|string[] $classes One or more classes to add to the class list.
	 *
	 * @return string[] $classes Array of classes.
	 */
	protected function get_body_class( $class = '' )
	{
		$classes  = [];
		$context  = $this->context();
		$section  = $this->section();
		$obj_type = Charcoal::str_to_ident( $this->context_type() );

		$tpl  = $this->ident();
		$ctrl = $this->parent_template()->ident();

		if ( $section->v('ident') ) {
			$classes[] = 'section--' . $section->v('ident');
		}

		if ( $obj_type ) {
			$classes[] = $obj_type;

			if ( $context->id() ) {
				$classes[] = $obj_type . '--' . $context->id();
			}
		}

		if ( $tpl ) {
			$classes[] = 'template--' . $tpl;
		}

		if ( $ctrl && $tpl !== $ctrl ) {
			$classes[] = 'template--' . $ctrl;
		}

		$classes = str_replace( '.', '-', $classes );
		# $classes = str_replace( $this->module(), '', $classes );

		return $this->filter_html_class( $classes, $class );
	}

	/**
	 * Filter the list of CSS classes.
	 *
	 * @param string[]  $classes An array of body classes.
	 * @param string    $class   A comma-separated list of additional classes added to the body.
	 *
	 * @return string[] $classes Array of classes.
	 */
	protected function filter_html_class( array $classes = [], $class = '' )
	{
		if ( ! empty( $class ) ) {
			if ( ! is_array( $class ) ) {
				$class = preg_split( '#\s+#', $class );
			}

			$classes = array_merge( $classes, $class );
		}

		return array_unique( $classes );
	}
}

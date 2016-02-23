<?php

/**
 * File: Agency Signature Trait
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Mathieu Ducharme <mat@locomotive.ca>
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2014-10-09
 */

namespace CMS;

use \Charcoal_Template;

/**
 * Trait: Agency Signature
 *
 * @package CMS\Objects
 */
trait Trait_Signature
{
	/**
	 * The agency's information.
	 *
	 * @var mixed[] {
	 *     @var string $title For metadata usage, represents advisory information
	 *                        related to the signature.
	 *     @var string $text  Publicly displayed content of the signature.
	 *     @var string $url   URL to the signature defining a hypertext link.
	 * }
	 * @see Property_JSON
	 */
	public $signature;

	/**
	 * Render the agency's signature.
	 *
	 * @return string
	 */
	public function made_by()
	{
		$prop = $this->p('signature');

		if ( $prop && is_a($prop, 'Property_Structure') ) {
			$entry = $prop->as_charcoal_properties();

			if ( is_array($entry) || is_a($entry, 'Traversable') ) {
				$entry = reset($entry);
			}

			$tpl = Charcoal_Template::get( 'signature', [ 'signature' => $entry ] );

			$tpl->set_template('<a target="_blank" href="{{ signature.url }}" title="{{ signature.title }}">{{& signature.text }}</a>');

			return $tpl->render();
		}

		return '';
	}
}

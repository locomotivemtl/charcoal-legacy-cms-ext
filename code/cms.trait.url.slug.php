<?php

/**
 * File: CMS URL Slug Trait
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2015-08-18
 */

namespace CMS;

/**
 * Trait: CMS URL Slug
 *
 * @package CMS\Objects
 */
trait Trait_Url_Slug
{

	/**
	 * ...
	 *
	 * @var string
	 * @see Property_String (l10n)
	 */
	public $slug;

	/**
	 * Compute the unique "slug" property
	 *
	 * The $slug property will be generated from the $title if no value is provided.
	 *
	 * @used-by $this::pre_save()
	 * @used-by $this::pre_update()
	 *
	 * @see CMS_Section::filter_unique_ident()
	 *
	 * @param string $context Either 'update' or (by default) 'save'.
	 */
	protected function filter_unique_slug( $context = null )
	{
		if ( ! in_array( $context, [ 'save', 'update' ] ) ) {
			$context = 'save';
		}

		if ( ! $this->p('slug')->l10n() ) {
			$this->slug = [ $this->lang() => $this->slug ];
		}

		foreach ( $this->slug as $lang => &$slug ) {
			if (
				! $old ||
				empty( $slug ) ||
				(
					( $old instanceof Charcoal_Object ) &&
					( ( $old->p('slug')->text([ 'lang'=> $lang ]) ) !== $slug )
				)
			) {
				$slug = generate_unique_object_ident( $this, $slug, [
					'lang'   => $lang,
					'target' => 'slug',
					'parent' => 'master'
				] );
			}
		}

		if ( ! $this->p('slug')->l10n() ) {
			$this->slug = reset($this->slug);
		}
	}
}

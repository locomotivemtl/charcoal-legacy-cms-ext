<?php

/**
 * File: Object URI Class
 *
 * @copyright  2016 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2016-02-25
 */

// Depdencies from Core Legacy
use \Charcoal\Trait_Url;

// Local Depdencies
use \CMS\Trait_Url_Slug;

// Depdencies from CMS Metadata Extension
use \CMS\Interface_Content_Metadata_Basic     as Interface_Metadata_Basic;
use \CMS\Interface_Content_Metadata_Keywords  as Interface_Metadata_Keywords;
use \CMS\Interface_Content_Metadata_OpenGraph as Interface_Metadata_OpenGraph;
use \CMS\Trait_Content_Metadata_Basic         as Trait_Metadata_Basic;
use \CMS\Trait_Content_Metadata_Keywords      as Trait_Metadata_Keywords;
use \CMS\Trait_Content_Metadata_OpenGraph     as Trait_Metadata_OpenGraph;

/**
 * Class: Object URI
 *
 * A modern variant of {@see Core_Object_URL}
 * using Traits.
 *
 * ## Inheritence
 *
 * #### Charcoal_Object
 *
 * Charcoal Properties:
 *
 * - $id
 * - $active
 *
 * #### Trait_URL_Slug
 *
 * Charcoal Properties:
 *
 * - $slug
 *
 * #### Trait_Metadata_Basic
 *
 * Charcoal Properties:
 *
 * - $meta_title
 * - $meta_description
 * - $meta_keywords
 *
 * #### Trait_Metadata_OpenGraph
 *
 * Charcoal Properties:
 *
 * - $meta_type
 * - $meta_image
 *
 * @package CMS\Objects
 */
class CMS_Object_URI extends Charcoal_Object implements
	Interface_Metadata_Basic,
	Interface_Metadata_Keywords,
	Interface_Metadata_OpenGraph
{
	use Trait_Url,
		Trait_Url_Slug,
		Trait_Metadata_Basic,
		Trait_Metadata_Keywords,
		Trait_Metadata_OpenGraph;

// Methods: Charcoal_Object + CMS\Trait_URL_Slug
// ==========================================================================

	/**
	 * {@inheritdoc}
	 */
	protected function pre_save( $properties = null )
	{
		$this->filter_unique_slug('save');

		return parent::pre_save($properties);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function pre_update()
	{
		$this->filter_unique_slug('update');

		return parent::pre_update();
	}



// Methods: CMS\Trait_Content_Metadata_* (charcoal-legacy-cms-meta)
// ==========================================================================

	/**
	 * Retrieve the name of the web site
	 * upon which the object resides.
	 *
	 * @see Interface_Metadata_OpenGraph
	 *
	 * @return string
	 */
	public function meta_site_name() {
		return CMS_Config::get_latest()->meta_site_name();
	}
}

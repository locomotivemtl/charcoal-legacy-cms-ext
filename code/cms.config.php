<?php

/**
 * File: CMS Site Configuration Class
 *
 * Holds the configuration for everything related to Content-Management
 * (default meta tags, analytics, social URLs, default contact information, etc.)
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Mathieu Ducharme <mat@locomotive.ca>
 * @since      Version 2015-08-10
 */

use \CMS\Interface_Content_Metadata_Basic     as Interface_Metadata_Basic;
use \CMS\Interface_Content_Metadata_OpenGraph as Interface_Metadata_OpenGraph;
use \CMS\Trait_Content_Metadata_Basic         as Trait_Metadata_Basic;
use \CMS\Trait_Content_Metadata_OpenGraph     as Trait_Metadata_OpenGraph;

/**
 * Class: CMS Site Configuration
 *
 * The latest (current) object can always be used by calling:
 *
 * ```php
 * $cfg = Pg_Config::get_latest();
 * ```
 *
 * Replaces Legacy's {@link //github.com/locomotivemtl/charcoal-legacy/blob/master/modules/cms/code/cms.config.php `CMS_Config`}.
 *
 * @package CMS\Objects
 */
class CMS_Config extends Charcoal_Object implements
	Interface_Metadata_Basic,
	Interface_Metadata_OpenGraph
{
	use CMS\Trait_Signature,
		CMS\Trait_Social_Web,
		Trait_Metadata_Basic,
		Trait_Metadata_OpenGraph;

    /**
     * Default "Front-Page" Section
     *
     * The section to load when reaching the default base URL.
     * Also, usually the section to load when clicking on the
     * main link / logo.
     *
     * @var mixed
     * @see Property_Object
     * @see CMS_Section
     */
    public $default_section;

    /**
     * Default Language
     *
     * @var string
     * @see Property_Lang
     */
    public $default_lang;

    /**
     * Default Primary Color
     *
     * @var string
     * @see Property_Color
     */
    public $default_color;

    /**
     * Google Analytics Tracking ID
     *
     * @var string
     * @see Property_String
     */
    public $google_analytics;

    /**
     * TypeKit Kit ID
     *
     * @var string
     * @see Property_String
     */
    public $typekit;

	/**
	 * The storage key.
	 *
	 * @var string
	 */
	protected static $_cache_key = 'cms-latest_config';

	/**
	 * Retrieve a singleton instance of Configuration object.
	 *
	 * @return $this
	 */
	public static function get_latest()
	{
		static $_cached;

		if ( ! $_cached instanceof self ) {
			if ( isset( Charcoal::$config['default_project_config'] ) && class_exists( Charcoal::$config['default_project_config'] ) ) {
				$default_config_class = Charcoal::$config['default_project_config'];
			}
			else {
				$default_config_class = get_called_class();
			}

			$_cached = Charcoal::obj( $default_config_class )->load_latest();
		}

		return $_cached;
	}

	/**
	 * Retrieve the first Configuration object from the database.
	 *
	 * @return $this
	 */
	public function load_latest()
	{
		$this->load_key('id', 1);

		return $this;
	}
}

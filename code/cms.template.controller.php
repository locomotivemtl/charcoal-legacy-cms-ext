<?php

/**
 * File: Base CMS Template Controller Class
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Mathieu Ducharme <mat@locomotive.ca>
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2015-08-06
 */

/**
 * Template Controller: Base Controller
 *
 * The CMS module's primary template controller. All variants should inherit this class.
 *
 * @package CMS\Objects
 */
class CMS_Template_Controller extends Charcoal_Template_Controller
{
	/**
	 * Keep a copy of the current section
	 *
	 * @var Charcoal_Object_Loader
	 */
	protected $_section;

	/**
	 * Keep a copy of the Section Object loader
	 *
	 * @var Charcoal_Object_Loader
	 */
	protected $_sections_loader;

	/**
	 * Keep a copy of the Snippets Object loader
	 *
	 * @var Charcoal_Object_Loader
	 */
	protected $_texts_loader;

	/**
	 * Keep a copy of the Template loader
	 *
	 * @var Charcoal_Template_Loader
	 */
	protected $_templates_loader;

	/**
	 * Keep a copy of the Widget Loader
	 *
	 * @var Charcoal_Widget_Loader
	 */
	protected $_widgets_loader;

	/**
	 * Retrieve the template controller's primary module
	 *
	 * @return string
	 */
	public function module()
	{
		return 'cms';
	}

	/**
	 * Set the current Section for the template controller
	 *
	 * @param CMS_Section $section
	 *
	 * @return $this
	 */
	public function set_section( $section )
	{
		$this->_section = $section;

		return $this;
	}

	/**
	 * Retrieve the template controller's current Section
	 *
	 * @return CMS_Section
	 */
	public function section()
	{
		if ( isset($this->_section) ) {
			return $this->_section;
		}

		$section_id = filter_input(INPUT_GET, 's', FILTER_SANITIZE_STRING);
		$section = Charcoal::obj('CMS_Section');

		if ( $section_id ) {
			$section->load($section_id);
		}

		$this->_section = $section;

		return $section;
	}

	/**
	 * Retrieve a singleton instance of the Object Loader for importing Section objects.
	 *
	 * Uses the "ident" property to load objects.
	 *
	 * @see CMS_Section
	 *
	 * @return Charcoal_Object_Loader
	 */
	public function sections()
	{
		if ( ! $this->_sections_loader ) {
			$this->_sections_loader = new Charcoal_Object_Loader('CMS_Section', 'ident', 'cache');
		}

		return $this->_sections_loader;
	}

	/**
	 * Retrieve a singleton instance of the Object Loader for importing Snippet objects.
	 *
	 * Uses the "ident" property to load objects.
	 *
	 * @see CMS_Text
	 *
	 * @return Charcoal_Object_Loader
	 */
	public function texts()
	{
		if ( ! $this->_texts_loader ) {
			$this->_texts_loader = new Charcoal_Object_Loader('CMS_Text', 'ident', 'cache');
		}

		return $this->_texts_loader;
	}

	/**
	 * Retrieve information about the project
	 *
	 * @return Pg_Config
	 */
	public function cfg()
	{
		return CMS_Config::get_latest();
	}

	/**
	 * Retrieve the site URL
	 *
	 * @return string
	 */
	public function base_url()
	{
		return Charcoal::$config['URL'];
	}

	/**
	 * Alias of $this->base_url()
	 */
	public function URL()
	{
		return $this->base_url();
	}

	/**
	 * Retrieve the current URL
	 *
	 * Builds a URL from Charcoal's HTTP and PHP's $_SERVER properties.
	 *
	 * @return string
	 */
	public function current_url()
	{
		return $this->base_url() . $_SERVER['REQUEST_URI'];
	}

	/**
	 * Retrieve the current language
	 *
	 * @return string A ISO 639-1 language code
	 */
	public function lang()
	{
		return _l();
	}

	/**
	 * Asset Getters
	 *
	 * Retrieve an asset, if it exists, of a specified type.
	 *
	 * ## Usage
	 *
	 * #### With Mustache
	 *
	 * ```mustache
	 * {{#assets.images}}test.png{{/assets.images}}
	 * ```
	 *
	 * ## Asset Types
	 *
	 * - Images
	 * - Styles
	 * - Scripts
	 * - Fonts
	 * - Files
	 *
	 * @see Charcoal\Asset For details on how assets are loaded relative to the filesystem.
	 *
	 * @param string $asset_mode Optional. An allowed Charcoal\Asset mode. Defaults to "url".
	 *
	 * @return Closure[] {
	 *     Returns an associative array of Closures that create a new instance
	 *     of Charcoal\Asset with the desired $file.
	 *
	 *     @type string                $file   The base file name and extension to lookup.
	 *     @type Mustache_LambdaHelper $lambda Unused.
	 *
	 *     @return Charcoal\Asset|string Returns a value based on $asset_mode or FALSE if it doesn't exist.
	 * }
	 */
	public function assets( $asset_mode = 'url' )
	{
		$lambdas = [
			'images' => function ( $file ) use ( $asset_mode ) {
				return new Charcoal\Asset('images', $file, $asset_mode);
			},
			'styles' => function ( $file ) use ( $asset_mode ) {
				return new Charcoal\Asset('styles', $file, $asset_mode);
			},
			'scripts' => function ( $file ) use ( $asset_mode ) {
				return new Charcoal\Asset('scripts', $file, $asset_mode);
			},
			'fonts' => function ( $file ) use ( $asset_mode ) {
				return new Charcoal\Asset('fonts', $file, $asset_mode);
			},
			'files' => function ( $file ) use ( $asset_mode ) {
				return new Charcoal\Asset('files', $file, $asset_mode);
			}
		];

		return $lambdas;
	}

	/**
	 * Retrieve the name of the template as a CSS-friendly class name.
	 *
	 * Replaces invalid characters, such as dots for underscores.
	 *
	 * @return string
	 */
	public function template_class()
	{
		$token   = $this->section()->template;
		$search  = [ "{$this->module()}.", '.' ];
		$replace = [ '', '_' ];

		if ( is_numeric($token) ) {
			$token = preg_replace( '/\D+/', '', $token );
			return $token;
		}
		else {
			$token = str_replace( $search, $replace,$token );
			$token = preg_replace( '/[^\w-]/', '', strtolower($token) );
			return $token;
		}

		return $token;
	}
}

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
	use Boilerplate_Trait_Content_Metadata,
		CMS\Trait_HTML_Classes;

	/**
	 * Keep a copy of the current context
	 *
	 * @var Charcoal_Object
	 */
	protected $_context;

	/**
	 * Keep a copy of the current section
	 *
	 * @var Charcoal_Object
	 */
	protected $_section;

	/**
	 * Keep a copy of the current context's template options
	 *
	 * @var mixed
	 */
	protected $_template_options;

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
	 * Keep track of the current Mustache context
	 *
	 * @var string
	 */
	private static $__mustache_scope = null;

	/**
	 * Keep track of the last Mustache context
	 *
	 * @var string
	 */
	private static $__last_mustache_scope;

	/**
	 * Default values for array tracking.
	 *
	 * @var (string|int)[]
	 */
	private static $__array_pointer_defaults = [
		'index' => 0,
		'count' => 0
	];

	/**
	 * Array pointers for tracking current index and collection count.
	 *
	 * @var mixed[][]
	 */
	private static $__array_pointers = [];

	/**
	 * Resolve additional request parameters related to the template controller.
	 *
	 * @return $this
	 */
	protected function resolve_request()
	{
		return $this;
	}

	/**
	 * Handle any additional request operations for the template controller.
	 *
	 * @return $this
	 */
	public function handle_request()
	{
		$this->resolve_request();

		return $this;
	}

	/**
	 * Enqueue assets (scripts & styles) related to the template controller.
	 *
	 * @return $this
	 */
	public function enqueue_assets()
	{
		return $this;
	}

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
	 * Reset the product iteration pointer
	 *
	 * @return $this
	 */
	protected function reset_mustache_scope()
	{
		self::$__mustache_scope = ( self::$__last_mustache_scope ?: null );

		return $this;
	}

	/**
	 * Set a label for the current Mustache rendering context.
	 *
	 * Useful for instances of `Charcoal_Template_Controller` when
	 * wanting to alter their output based on where it's rendering.*
	 *
	 * @param string|int $scope
	 *
	 * @return $this
	 */
	protected function set_mustache_scope( $scope = null )
	{
		self::$__last_mustache_scope = self::$__mustache_scope;

		self::$__mustache_scope = $scope;

		return $this;
	}

	/**
	 * Get the current Mustache rendering context.
	 *
	 * @return string|int
	 */
	protected function get_mustache_scope()
	{
		return self::$_mustache_scope;
	}

	/**
	 * Set an iterator. Will replace any existing iterators.
	 *
	 * @see self::get_iterator() To learn how to use internal iterators.
	 *
	 * @param string  $name
	 * @param mixed[] $options Optional.
	 *
	 * @return $this
	 */
	protected function set_iterator( $name, $options = [] )
	{
		if ( is_array( $options ) && ! empty( $options ) ) {
			$options = parse_config( self::$__array_pointer_defaults, $options );
		}
		else {
			$options = self::$__array_pointer_defaults;
		}

		self::$__array_pointers[ $name ] = $options;

		return $this;
	}

	/**
	 * Retrieve an iterator.
	 *
	 * Array iterations in Mustache are "silent" on the template-end.
	 * To track the progress of the iterator or to execute certain features
	 * within a loop, you must implement a method much—like a `next()`—that
	 * advances an internal array pointer (this instance retrieved from this
	 * method). This "increment/decrement pointer" method must be accessible
	 * as a template tag.
	 *
	 * @see PlaisirsGastronomiques\Template_Controller_Pg_Product::iterate_cooking_method() For an example.
	 *
	 * @param string     $name
	 * @param string|int $index Optional. If TRUE, "next", or "+1", increment value.
	 *                          If FALSE, "prev", "previous", or "-1", decrement value.
	 *                          If integer, set index to provided value.
	 *
	 * @return mixed[] Returns the iterator, prior to any changes to the index.
	 */
	protected function get_iterator( $name, $index = null )
	{
		if ( isset( self::$__array_pointers[ $name ] ) ) {
			$iterator  =  self::$__array_pointers[ $name ];
			$reference = &self::$__array_pointers[ $name ];

			if ( ! is_null( $index ) ) {
				$has_changed = false;

				if ( in_array( $index, [ true, 'next', '+1' ] ) ) {
					$reference['index']++;
					$has_changed = true;
				}
				elseif ( in_array( $index, [ false, 'prev', 'previous', '-1' ] ) ) {
					$reference['index']--;
					$has_changed = true;
				}
				elseif ( is_int( $index ) ) {
					$reference['index'] = $index;
					$has_changed = true;
				}

				if ( $has_changed ) {
					if ( $reference['index'] < 0 ) {
						$reference['index'] = ( $reference['count'] + $reference['index'] );
					}

					if ( $reference['index'] > $reference['count'] ) {
						$reference['index'] = ( ( $reference['index'] - 1 ) - $reference['count'] );
					}
				}
			}

			return $iterator;
		}

		return [];
	}

	/**
	 * Set the current context for the template controller
	 *
	 * @param Charcoal_Object $context
	 *
	 * @return $this
	 */
	public function set_context( $context )
	{
		$this->_context = $context;

		return $this;
	}

	/**
	 * Retrieve the context's template options.
	 *
	 * @uses self::get_context_for_template_options()
	 *
	 * @param bool $reload Optional. Reload the template options from the context.
	 *
	 * @return mixed
	 */
	public function get_template_options( $reload = false )
	{
		if ( ! isset( $this->_template_options ) || $reload ) {
			$options = $this->get_context_for_template_options()->p('template_options');

			if ( $options ) {
				$this->_template_options = $options->as_charcoal_object();
			}
			else {
				$this->_template_options = false;
			}
		}

		return $this->_template_options;
	}

	/**
	 * Retrieve the desired context for template options.
	 *
	 * If the {@see self::context()} isn't a Charcoal Object with a "template_options" property,
	 * this is the method that should be re-implemented in your sub-template controller.
	 *
	 * @used-by self::get_template_options()
	 *
	 * @return Charcoal_Object
	 */
	protected function get_context_for_template_options()
	{
		return $this->context();
	}

	/**
	 * Get the current object relative to the context
	 *
	 * This method is meant to be reimplemented in a child template controller
	 * to return the resolved object that the module considers "the context".
	 *
	 * @return Charcoal_Object Chainable
	 */
	public function context()
	{
		if ( ! $this->_context ) {
			$this->_context = $this->section();
		}

		return $this->_context;
	}

	/**
	 * Get the current object's type relative to the context
	 *
	 * This method is meant to be reimplemented in a child template controller
	 * to return the resolved object that the module considers "the context".
	 *
	 * @return string
	 */
	public function context_type()
	{
		return $this->context()->obj_type();
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
		return $this->base_url() . ltrim( $this->context()->url(), '/\\' ) /* getenv('REQUEST_URI') */;
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
	 *     @type Mustache_LambdaHelper $lambda An instance of the Mustache LambdaHelper object.
	 *
	 *     @return Charcoal\Asset|string Returns a value based on $asset_mode or FALSE if it doesn't exist.
	 * }
	 */
	public function assets( $asset_mode = 'url' )
	{
		$lambdas = [
			'images'  => function ( $file, Mustache_LambdaHelper $lambda ) use ( $asset_mode ) {
				return new Charcoal\Asset('images', $lambda->render($file), $asset_mode);
			},
			'styles'  => function ( $file, Mustache_LambdaHelper $lambda ) use ( $asset_mode ) {
				return new Charcoal\Asset('styles', $lambda->render($file), $asset_mode);
			},
			'scripts' => function ( $file, Mustache_LambdaHelper $lambda ) use ( $asset_mode ) {
				return new Charcoal\Asset('scripts', $lambda->render($file), $asset_mode);
			},
			'fonts'   => function ( $file, Mustache_LambdaHelper $lambda ) use ( $asset_mode ) {
				return new Charcoal\Asset('fonts', $lambda->render($file), $asset_mode);
			},
			'files'   => function ( $file, Mustache_LambdaHelper $lambda ) use ( $asset_mode ) {
				return new Charcoal\Asset('files', $lambda->render($file), $asset_mode);
			}
		];

		return $lambdas;
	}

	/**
	 * Retrieve a utility for interacting with the context's translations, if any.
	 *
	 * @param bool $include_self Optional
	 *
	 * @return mixed[]
	 */
	public function translations( $include_self = false )
	{
		$context      = $this->context();
		$translations = new ArrayIterator;

		if ( $include_self ) {
			$languages = Charcoal::langs();
		}
		else {
			$languages = get_alternate_languages();
		}

		$query_uri = parse_url( getenv('REQUEST_URI'), PHP_URL_QUERY );

		foreach ( $languages as $code ) {
			$url = $context->url( $code );

			if ( empty( $url ) ) {
				continue;
			}
			elseif ( ! empty( $query_uri ) ) {
				$url .= '?' . $query_uri;
			}

			$lang   = get_language_config( $code );
			$label  = l10n( $lang['label'] );
			$locale = ( isset( $lang['locale'] ) ? $lang['locale'] : $code );
			$_abbr  = ( isset( $lang['abbreviation'] ) ? $lang['abbreviation'] : mb_strtoupper( $code ) );
			$abbr   = l10n( $_abbr );

			$label_l7d = l10n( $lang['label'], null, $code );
			$abbr_l7d  = l10n( $_abbr, null, $code );

			$translations[ $code ] = [
				'is_current' => ( _l() === $code ),
				'code'       => $code,
				'localized'  => [
					'abbr'   => '<abbr title="' . $label_l7d . '">' . $abbr_l7d . '</abbr>',
					'label'  => $label_l7d
				],
				'abbr'       => '<abbr title="' . $label . '">' . $abbr . '</abbr>',
				'label'      => $label,
				'locale'     => $locale,
				'hreflang'   => ( $locale ? ' hreflang="' . $locale . '"' : false ),
				'href'       => ' href="' . $url . '"',
				'full_href'  => ' href="' . ( $this->base_url() . $url ) . '"',
				'url'        => $url,
				'full_url'   => $this->base_url() . $url
			];
		}

		return $translations;
	}

	/**
	 * Alias of self::translations()
	 */
	public function all_translations()
	{
		return $this->translations(true);
	}

	/**
	 * Alias of self::translations()
	 */
	public function translation()
	{
		return $this->translations();
	}

	/**
	 * Pre-render, with Mustache, a block of text.
	 *
	 * Would be called `render()` but currently used by {@see Charcoal_Base::render())
	 * for Charcoal-style pattern remplacements.
	 *
	 * @return Closure {
	 *     Returns a Closure that provides a way to recursively render any Mustache tags
	 *     within the block of text that will be rendered.
	 *
	 *     Without this `pre_render()` method, Mustache would only render the initial tag
	 *     and ignore any Mustache tags within.
	 *
	 *     @type string                $text   Block of text.
	 *     @type Mustache_LambdaHelper $lambda An instance of the {@see Mustache_LambdaHelper} object.
	 *
	 *     @return string
	 * }
	 */
	public function pre_render()
	{
		return function ( $text, Mustache_LambdaHelper $lambda ) {
			return $lambda->render( $text );
		};
	}

	/**
	 * Output additional data in the `<head>` of the HTML document.
	 *
	 * This method is meant to be reimplemented in a child template controller.
	 *
	 * @see cms/assets/templates/cms.inc.head.php
	 *
	 * @param string|string[] $output Optional. Additional data to output in the `<head>` element.
	 * @param bool            $after  Optional. Wether to output the extra data before or after default data.
	 *
	 * @return Closure {
	 *     Returns a Closure that can alter the default JS operations..
	 *
	 *     @type string                $text   The contents of the `<head>` element from the template.
	 *     @type Mustache_LambdaHelper $lambda An instance of the Mustache LambdaHelper object.
	 *
	 *     @uses string|string[]       $output Optional. Additional data to output in the `<head>` element.
	 *     @uses bool                  $after  Optional. Wether to output the extra data before or after default data.
	 *
	 *     @return string
	 * }
	 */
	public function filter_document_head( $output = '', $after = true )
	{
		return function ( $text, Mustache_LambdaHelper $lambda ) use ( $output, $after ) {
			if ( is_array( $output ) ) {
				# $text = explode("\n", preg_replace('#^\t+#m', '', $text));
				$output = implode("\n\t\t", $output);
			}

			return $lambda->render( $after ? $text . $output : $output . $text );
		};
	}

	/**
	 * Output additional data before the closing `</body>` tag of the HTML document.
	 *
	 * @see cms/assets/templates/cms.inc.foot.php
	 *
	 * @param string|string[] $output Optional. Additional data to output before the closing `</body>` element.
	 * @param bool            $after  Optional. Wether to output the extra data before or after default data.
	 *
	 * @return Closure {
	 *     Returns a Closure that can alter the default JS operations..
	 *
	 *     @type string                $text   The contents of the `<head>` element from the template.
	 *     @type Mustache_LambdaHelper $lambda An instance of the Mustache LambdaHelper object.
	 *
	 *     @uses string|string[]       $output Optional. Additional data to output before the closing `</body>` element.
	 *     @uses bool                  $after  Optional. Wether to output the extra data before or after default data.
	 *
	 *     @return string
	 * }
	 */
	public function filter_document_foot( $output = '', $after = false )
	{
		return function ( $text, Mustache_LambdaHelper $lambda ) use ( $output, $after ) {
			if ( is_array( $output ) ) {
				# $text = explode("\n", preg_replace('#^\t+#m', '', $text));
				$output = implode("\n\t\t", $output);
			}

			return $lambda->render( $after ? $text . $output : $output . $text );
		};
	}

	/**
	 * Filter the output of the default Google Analytics tracking statements.
	 *
	 * This method is meant to be reimplemented in a child template controller
	 * to either render additional JS operations or alter the default ones.
	 *
	 * @return Closure {
	 *     Returns a Closure that can alter the default JS operations..
	 *
	 *     @type string                $text   The default `ga()` calls.
	 *     @type Mustache_LambdaHelper $lambda An instance of the Mustache LambdaHelper object.
	 *
	 *     @return string
	 * }
	 */
	public function filter_google_analytics()
	{
		return function ( $text, Mustache_LambdaHelper $lambda ) {
			return $lambda->render($text);
		};
	}

}

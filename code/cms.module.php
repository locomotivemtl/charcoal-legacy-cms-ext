<?php

/**
 * File: CMS Module Class
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Mathieu Ducharme <mat@locomotive.ca>
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2015-08-06
 */

/**
 * Class: CMS Module
 *
 * The CMS module's front-end controller.
 *
 * Replaces Legacy's {@link //github.com/locomotivemtl/charcoal-legacy/blob/master/modules/cms/code/cms.module.php `CMS_Module`}.
 *
 * @package CMS\Modules
 */
class CMS_Module extends Charcoal_Module
{
	/**
	 * Determine if the template controller is loaded.
	 *
	 * @var bool
	 */
	protected static $_is_controller_loaded;

	/**
	 * Session key for storage.
	 *
	 * @var string
	 */
	public static $session_key = 'cms.module';

	/**
	 * Module initialisation
	 *
	 * This function should act as both the initialization of the module and the front-page main controller.
	 *
	 * ## Options
	 * - default_action
	 * - default_section
	 * - default_lang
	 *
	 * @param mixed[] $options Optional. An associative array of options to influence the front-end.
	 */
	public static function init( array $options = [] )
	{
		// Make sure a session is started at all time. For tokens, some cache, user data, etc.
		if ( ! session_id() ) {
			session_start();
		}

		// Load the request parameters from $_GET
		$action     = filter_input(INPUT_GET, 'action',     FILTER_SANITIZE_STRING);
		$section_id = filter_input(INPUT_GET, 'section_id', FILTER_SANITIZE_STRING);
		$language   = filter_input(INPUT_GET, 'lang',       FILTER_SANITIZE_STRING);

		// Prepare default request options
		self::parse_request( $options, $action, $section_id, $language );

		self::set_language( $language );

		self::resolve_request( $action, $section_id );
	}

	/**
	 * Parse the request parameters if any fallbacks must be applied.
	 *
	 * @param mixed[] $options
	 * @param string  &$action
	 * @param int     &$section_id
	 * @param string  &$language
	 */
	protected static function parse_request( $options, &$action, &$section_id, &$language )
	{
		$config = CMS_Config::get_latest();

		$defaults = [
			'default_action'  => null,
			'default_section' => null,
			'default_lang'    => null
		];

		$options = array_merge( $defaults, $options );

		// If there is no requested "action" or "section_id",
		// we assume the request is for the index.
		if ( ! $action && ! $section_id ) {
			if ( $options['default_section'] ) {
				$section_id = $options['default_section'];
			}
			elseif ( $config->default_section ) {
				$section_id = $config->default_section;
			}
		}

		// Resolve the current language
		if ( ! $language || ! in_array( $language, Charcoal::langs() ) ) {
			if ( $options['default_lang'] ) {
				$language = $options['default_lang'];
			}
			elseif ( $config->default_lang ) {
				$language = $config->default_lang;
			}
			elseif ( ! empty(Charcoal::$config['default_language']) ) {
				$language = Charcoal::$config['default_language'];
			}
			else {
				$language = 'fr';
			}
		}
	}

	/**
	 * Resolve the request parameters and render the view.
	 *
	 * @param string $action
	 * @param string $language
	 */
	protected static function resolve_request( &$action, &$section_id )
	{
		if ( $section_id ) {
			$class = 'CMS_Section';

			$key = self::resolve_loader_key($class, $section_id);

			$section_loader = new Charcoal_Object_Loader($class, $key);

			$section = $section_loader->{ $section_id };

			if ( $section->template ) {
				// What to do?
			}

			$tpl = Charcoal_Template::get($section->template);

			// Section is already loaded, let's tell the controller about it.
			$tpl->controller()
				->set_section($section)
				->handle_request()
				->enqueue_assets();

			self::is_controller_loaded(true);

			echo $tpl->render();
		}
		else if ( $action ) {
			// By action
			Charcoal::exec($action, $_REQUEST);
		}
		else {
			// By nothing (404 page not found). This should never happen
			exit('404');
		}
	}

	/**
	 * Determine if the template controller is loaded.
	 *
	 * @param bool|null $state Set the template controller's state.
	 *
	 * @return bool
	 */
	public static function is_controller_loaded( $state = null )
	{
		if ( is_bool( $state ) ) {
			self::$_is_controller_loaded = $state;
		}

		return self::$_is_controller_loaded;
	}

	/**
	 * Apply the provided language and set locale
	 *
	 * @param string $lang The desired language
	 */
	protected static function set_language( $lang )
	{
		$languages = &Charcoal::$config['languages'];

		// Set up the language and the required CSV file
		$l = Charcoal_L10n::get();
		$l->set_lang($lang);
		$l->add_resource_csv('pg', $lang);

		if ( isset( $languages[ $lang ]['locale'] ) ) {
			$locale = str_replace( '-', '_', $languages[ $lang ]['locale'] );
		}
		else {
			$locale = $lang;
		}

		setlocale( LC_ALL, $locale );

		_a( 'one-invalid-field', [
				'en' => 'One field is invalid.',
				'fr' => 'Un champ est invalide.'
		] );

		_a( 'many-invalid-fields', [
				'en' => '%d fields are invalid.',
				'fr' => '%d champs ne sont pas valides.'
		] );

		_a( 'describe-invalid-field', [
				'en' => 'A problem has occurred while processing your submission.',
				'fr' => 'Un problème est survenu lors de l’enregistrement de votre soumission.'
		] );

		_a( 'describe-invalid-fields', [
				'en' => 'Problems occured while processing your submission.',
				'fr' => 'Des problèmes ont eu lieu lors de l’enregistrement de votre soumission.'
		] );

		_a( 'unknown-error', [
				'en' => 'An error occured while processing your submission.',
				'fr' => 'Une erreur est survenue lors du traitement de votre soumission.'
		] );
	}

	/**
	 * Resolve the provided object key loader
	 * (whether it uses {@see Trait_Url_Slug}'s property)
	 *
	 * @param string|Charcoal_Object $obj
	 * @param string|int             $ident    A value to match against.
	 * @param string|null            $fallback Optional.
	 *
	 * @return string|null
	 */
	public static function resolve_loader_key( $obj, $ident, $fallback = 'ident' )
	{
		if ( is_string($obj) ) {
			$obj = Charcoal::obj($obj);
		}

		$prop = $obj->p('slug');

		if ( ! is_numeric($ident) ) {
			if ( $prop ) {
				$key = 'slug';

				if ( $prop->l10n() ) {
					$key .= '_' . _l();
				}

				return $key;
			}

			$prop = $obj->p( $fallback );

			if ( $prop ) {
				$key = $fallback;

				if ( $prop->l10n() ) {
					$key .= '_' . _l();
				}

				return $key;
			}
		}

		return null;
	}
}

<?php

/**
 * File: Front-end Object Template Trait
 *
 * @copyright 2015 Locomotive
 * @license   PROPRIETARY
 * @link      http://charcoal.locomotive.ca
 * @author    Chauncey McAskill <chauncey@locomotive.ca>
 * @since     Version 2015-09-06
 */

/**
 * Trait: Front-end Object Template
 *
 * @package CMS\Objects
 */
trait CMS_Trait_Template
{
	/**
	 * Template Views
	 *
	 * @var string
	 * @see Property_Object
	 * @see Charcoal_Template
	 */
	public $template;

	/**
	 * Template Customizations
	 *
	 * Custom fields for a {@see Charcoal_Object} can be defined
	 * by the object's config of the "template_options" property.
	 * Further options can be defined by the associated
	 * {@see Charcoal_Template_Controller}'s properties.
	 *
	 * @var mixed
	 * @see Property_CMS_Template_Options
	 * @see Charcoal_Template_Controller
	 */
	public $template_options;

	/**
	 * Whether the "template_options" property has imported the
	 * template's and view controller's properties.
	 *
	 * @var boolean
	 */
	private $loaded_template_options = false;

	/**
	 * Generate a "nonce" token.
	 *
	 * @return string
	 */
	public static function get_token()
	{
		return Charcoal::token($this->obj_type() . '-' . 'template_options');
	}

	/**
	 * Retrieve the Charcoal Action that processes template option reloading.
	 *
	 * @return string
	 */
	public function get_template_reload_action()
	{
		return 'cms.action.template.reload';
	}

	/**
	 * {@inheritdoc}
	 *
	 * Alter the object config to inject the template's properties.
	 *
	 * @return Charcoal_Config
	 */
	public function config()
	{
		$obj_config  = parent::config();

		if (
			$this->template &&
			! $this->loaded_template_options &&
			class_exists('Property_Structure')
		) {
			$this->loaded_template_options = true;

			$properties   = $obj_config['properties'];
			$struct_prop  = $this->p('template_options');
			$struct_extra = $struct_prop->template_config();

			if ( ! empty($struct_extra) ) {
				$struct_config = array_merge($properties['template_options'], $struct_extra);

				$properties['template_options'] = $struct_config;

				$obj_config['properties'] = $properties;

				self::$_config[ $this->obj_type() ] = $obj_config;
			}
		}

		return $obj_config;
	}
}

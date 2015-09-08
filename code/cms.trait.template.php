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
	 * Generate a "nonce" token.
	 *
	 * @return string
	 */
	public static function get_token()
	{
		return Charcoal::token('pg-template_options');
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
}

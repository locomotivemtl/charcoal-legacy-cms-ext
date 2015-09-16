<?php

/**
 * File: 404 Error Template Controller Class
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2015-09-16
 */

/**
 * Template Controller: 404 Error Controller
 *
 * Handles project-wide "Not Found" responses.
 *
 * @package CMS\Objects
 */
class Template_Controller_404 extends CMS_Template_Controller
{
	use CMS_Trait_Template_Controller_Error,
		CMS_Trait_Template_Controller_404;
}

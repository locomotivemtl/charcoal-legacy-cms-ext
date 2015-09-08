<?php

/**
 * File : Template Options Property Class
 *
 * @copyright © Locomotive 2015
 * @license   LGPL
 * @link      http://charcoal.locomotive.ca
 * @author    Chauncey McAskill <chauncey@locomotive.ca>
 * @since     Version 2015-09-06
 */

/**
 * Class: Template Options Property
 *
 * @package CMS\Properties
 */
class Property_CMS_Template_Options extends Property_Json
{
	/**
	 * {@inheritdoc}
	 *
	 * It's at this moment the property meets its maker.
	 */
	public function set_obj( Charcoal_Base $obj )
	{
		parent::set_obj($obj);

		$this->load_template_config();

		return $this;
	}

	/**
	 * Import the template properties from the current template.
	 *
	 * @return $this
	 */
	protected function load_template_config()
	{
		$obj  = $this->obj();
		$prop = $obj->p('template');

		if ( $prop && ( $template = $prop->val() ) ) {
			$tpl  = Charcoal_Template::get( $template );
			$ctrl = $tpl->controller();

			$template_classes = $this->filter_template_classes([ get_class( $tpl ), get_class( $ctrl ) ]);
			$default_classes  = $this->get_ignored_template_classes();

			foreach	( $template_classes as $class ) {
				if ( ! in_array( $class, $default_classes ) ) {
					$config = $this->load_config( $class );
					$data   = $config['data'];

					if ( isset( $config['properties'] ) ) {
						$data['fields_available'] = $config['properties'];
					}

					if ( isset( $data ) ) {
						$this->set_data( $data );
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Retrieve a list of classes (Template and Template Controller-based)
	 * to be ignored when importing their Charcoal Properties.
	 *
	 * @return string[]
	 */
	protected function get_ignored_template_classes()
	{
		return [ 'Charcoal_Template', 'Charcoal_Template_Controller' ];
	}

	/**
	 * Retrieve a list of classes (Template and Template Controller-based)
	 * to be loaded for their Charcoal Properties.
	 *
	 * @param (Charcoal_Template|Charcoal_Template_Controller)[] $classes Template and Template Controller instances to import Charcoal Properties from.
	 *
	 * @return (Charcoal_Template|Charcoal_Template_Controller)[]
	 */
	protected function filter_template_classes($classes)
	{
		return $classes;
	}
}

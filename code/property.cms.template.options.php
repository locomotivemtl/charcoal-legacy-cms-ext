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
	 * Store of imported template and view controller properties.
	 *
	 * @var array
	 */
	private $template_options = [];

	/**
	 * Whether the property has imported the template's and view controller's properties.
	 *
	 * @var boolean
	 */
	private $loaded_template_options = false;

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
	 * @return array
	 */
	public function template_config()
	{
		if ( ! isset($this->template_options) ) {
			$this->load_template_config();
		}

		return $this->template_options;
	}

	/**
	 * Import the template properties from the current template.
	 *
	 * @return $this
	 */
	protected function load_template_config()
	{
		$obj = $this->obj();

		if (
			$obj->template &&
			! $this->loaded_template_options &&
			class_exists('Property_Structure')
		) {
			$this->loaded_template_options = true;

			$tpl  = Charcoal::obj('CMS_Template')->load( $obj->template );
			$prop = $tpl->p('template_config');

			$view = Charcoal_Template::get( $obj->template );
			$ctrl = $view->controller();

			$template_classes = $this->filter_template_classes(
				array_merge(
					( $prop && $prop->val() ? [ $prop ] : [] ),
					[ $view, $ctrl ]
				)
			);
			$default_classes  = $this->get_ignored_template_classes();

			foreach	( $template_classes as $class ) {
				$class_name = ( is_object($class) ? get_class($class) : $class );

				if ( ! in_array( $class_name, $default_classes ) ) {
					$config = ( $class instanceof Property_JSON ? $class->entries() : $this->load_config( $class_name ) );
					$data   = ( empty( $config['data'] ) ? [] : $config['data'] );

					if ( ! empty( $config['properties'] ) ) {
						$data['fields_available'] = $config['properties'];
					}

					if ( ! empty( $data ) ) {
						$this->set_data( $data );
					}
				}
			}

			$this->template_options = $this->structured_data();
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

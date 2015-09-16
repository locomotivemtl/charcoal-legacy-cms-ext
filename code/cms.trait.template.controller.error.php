<?php

/**
 * File: CMS Base Error Trait
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2015-09-16
 */

/**
 * Trait: Base Error
 *
 * The CMS module's controller for handling project-wide "Not Found" responses.
 *
 * @package CMS\Objects
 */
trait CMS_Trait_Template_Controller_Error
{
	/**
	 * Keep a copy of the fake 404 section
	 *
	 * @var Charcoal_Object
	 */
	protected $_error_obj;

	/**
	 * Retrieve the error controller's code
	 *
	 * @return string
	 */
	abstract public function error_code();

	/**
	 * Retrieve the template controller's 404 object
	 *
	 * @return Charcoal_Object
	 */
	public function get_error()
	{
		if ( ! isset( $this->_mock_obj ) ) {
			$obj  = Charcoal::obj('CMS_Section');
			$cfg  = $obj->load_config( $this->error_code() );
			$url  = $this->current_url();
			$data = $cfg['data'];

			if ( $obj->p('external_url')->l10n() ) {
				foreach ( Charcoal::langs() as $lang ) {
					$data['external_url'][ $lang ] = $url;
				}
			}
			else {
				$data['external_url'] = $url;
			}

			$cfg['data'] = $data;

			if ( isset( $cfg['data'] ) ) {
				$obj->set_data( $cfg['data'] );
			}

			$this->_error_obj = $obj;
		}

		return $this->_error_obj;
	}

	/**
	 * Retrieve the template controller's current Section
	 *
	 * @return CMS_Section
	 */
	public function section()
	{
		if ( ! isset( $this->_section ) ) {
			$this->_section = $this->get_error();
		}

		return $this->_section;
	}

	/**
	 * {@inheritdoc}
	 */
	public function current_url()
	{
		return $this->base_url() . ltrim( getenv('REQUEST_URI'), '/\\' );
	}

}

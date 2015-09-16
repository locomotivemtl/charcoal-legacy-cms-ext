<?php

/**
 * File: CMS 404 Error Trait
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2015-09-16
 */

/**
 * Trait: 404 Error
 *
 * The CMS module's trait for handling project-wide "Not Found" responses.
 *
 * @package CMS\Objects
 */
trait CMS_Trait_Template_Controller_404
{
	/**
	 * {@inheritdoc}
	 */
	public function error_code()
	{
		return '404';
	}

	/**
	 * Retrieve `mailto:` email address, subject, and body
	 *
	 * @return string
	 */
	public function mailto()
	{
		_a( '404-subject', [
			'en' => 'Page not found on %s',
			'fr' => 'Page non trouvée sur %s'
		] );

		_a( '404-body', [
			'en' => 'The following page may have moved or is no longer available: %s',
			'fr' => 'La page suivante est peut-être déplacé ou n’est plus disponible : %s'
		] );

		$email = $this->cfg()->p('contact_email')->text();

		if ( $email ) {
			$subject = sprintf( _t('404-subject'), $this->base_url() );
			$subject = rawurlencode( strip_tags( html_entity_decode( $subject ) ) );

			$body = sprintf( _t('404-body'), $this->current_url() );
			$body = rawurlencode( strip_tags( html_entity_decode( $body ) ) );

			return $email . '?subject=' . $subject . '&amp;body=' . $body;
		}
		else {
			return '';
		}
	}
}

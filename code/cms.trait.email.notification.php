<?php

/**
 * File: Sendable Notification Email Trait
 *
 * @copyright 2016 Locomotive
 * @license   PROPRIETARY
 * @link      http://charcoal.locomotive.ca
 * @author    Chauncey McAskill <chauncey@locomotive.ca>
 * @since     Version 2016-02-26
 */

/**
 * Trait: Sendable Notification Email
 *
 * @package CMS\Objects
 */
trait CMS_Trait_Email_Notification
{
	/**
	 * Send an entry notification email to the administrators.
	 *
	 * @return boolean TRUE if the email was successfully sent
	 */
	public function send_notification_email()
	{
		$category = $this->category();

		if ( ! $this->before_send_notification_email( $category ) ) {
			return false;
		}

		$template   = $this->build_notification_email_template( $category );
		$rich_body  = $this->render_notification_email_rich_template( $template, $category );
		$plain_body = $this->render_notification_email_plain_template( $template, $category );

		$email = $this->create_notification_email_object();

		if ( $template->ident() ) {
			$email->template = $template->ident();
		}

		if ( $rich_body ) {
			$email->msg_html = $rich_body;
		}

		if ( $plain_body ) {
			$email->msg_txt = $plain_body;
		}

		$response = $email->send();

		return $this->after_send_notification_email( $response );
	}

	/**
	 * Triggered before sending the notification email to the administrators.
	 *
	 * This event can cancel the send process.
	 *
	 * @param  CMS_Contact_Category|null &$category The notification's related category, if any.
	 * @return boolean Whether to proceed (TRUE) with sending the email or not (FALSE).
	 */
	protected function before_send_notification_email( &$category )
	{
		if ( ! $category || ! is_a($category, 'Charcoal_Base') || ! $category->v('send_confirmation_email') ) {
			// This category was configured to NOT send notification email
			return false;
		}

		// Cache currently active language,
		// to be restored at the end.
		$this->_current_lang = _l();

		// Set Charcoal to user's language preference
		$l = Charcoal_L10n::get();
		$l->set_lang( $this->user_lang );

		return true;
	}

	/**
	 * Triggered after sending the notification email to the administrators.
	 *
	 * This event receives the success/failure response from sending the email.
	 *
	 * @param  boolean $is_sent Whether the email was sent (TRUE) or not (FALSE).
	 * @return boolean Whether the email was sent (TRUE) or not (FALSE).
	 */
	protected function after_send_notification_email( $is_sent )
	{
		// Restore original language settings
		if ( $this->_current_lang ) {
			$l = Charcoal_L10n::get();
			$l->set_lang( $this->_current_lang );

			$this->_current_lang = null;
		}

		return $is_sent;
	}

	/**
	 * Create the email entry for the administrators.
	 *
	 * @return Charcoal_Email
	 */
	protected function create_notification_email_object()
	{
		$category = $this->category();

		$email           = new Charcoal_Email;
		$email->to       = $this->get_notified_email_address();
		$email->reply_to = $this->get_lead_email_address();

		if ( $category ) {
			$email->subject = $category->p('confirmation_email_subject')->text();
			$email->from    = $category->p('confirmation_email_from')->text();
			$email->cc      = $category->v('confirmation_email_cc');
			$email->bcc     = $category->v('confirmation_email_bcc');
		}

		return $email;
	}

	/**
	 * Build the notification email template.
	 *
	 * @param  CMS_Contact_Category|null &$category The notification's related category, if any.
	 * @return Charcoal_Template_Controller
	 */
	protected function build_notification_email_template( &$category )
	{
		return Charcoal_Template::get( $category->v('confirmation_email_template') );
	}

	/**
	 * Render the notification email template, for rich-text (HTML).
	 *
	 * @param  Charcoal_Template_Controller  $template The template object to render.
	 * @param  CMS_Contact_Category|null    &$category The notification's related category, if any.
	 * @return Charcoal_Template_Controller
	 */
	protected function render_notification_email_rich_template( $template, &$category )
	{
		$template->controller()->set_context( $this );

		return $template->render();
	}

	/**
	 * Render the notification email template, for plain-text.
	 *
	 * @param  Charcoal_Template_Controller  $template The template object to render.
	 * @param  CMS_Contact_Category|null    &$category The notification's related category, if any.
	 * @return Charcoal_Template_Controller
	 */
	protected function render_notification_email_plain_template( $template, &$category )
	{
		unset( $template, $category );

		$body = '';

		foreach ($this->properties() as $propIdent => $property) {
			if (is_a($property, 'Charcoal_Property') && $property->active()) {
				if (!isset($property->public_access) || $property->public_access) {
					$body .= $property->label() . ":\n" . $property->text() . "\n\n";
				}
			}
		}

		return strip_html( $body );
	}

	/**
	 * Filter the email address(es) of the notified (administrators).
	 *
	 * @return string|array
	 */
	protected function get_notified_email_address()
	{
		return [];
	}
}

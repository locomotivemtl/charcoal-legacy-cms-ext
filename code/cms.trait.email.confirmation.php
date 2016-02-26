<?php

/**
 * File: Sendable Confirmation Email Trait
 *
 * @copyright 2016 Locomotive
 * @license   PROPRIETARY
 * @link      http://charcoal.locomotive.ca
 * @author    Chauncey McAskill <chauncey@locomotive.ca>
 * @since     Version 2016-02-26
 */

/**
 * Trait: Sendable Confirmation Email
 *
 * @package CMS\Objects
 */
trait CMS_Trait_Email_Confirmation
{
	/**
	 * Send an entry confirmation email to the lead.
	 *
	 * @return boolean TRUE if the email was successfully sent
	 */
	public function send_confirmation_email()
	{
		$category = $this->category();

		if ( ! $this->before_send_confirmation_email( $category ) ) {
			return false;
		}

		$email = $this->create_confirmation_email_object();
		$email->msg_html = $this->render_confirmation_email_template( $category );

		$response = $email->send();

		return $this->after_send_confirmation_email( $response );
	}

	/**
	 * Triggered before sending the confirmation email to the lead.
	 *
	 * This event can cancel the send process.
	 *
	 * @param  CMS_Contact_Category|null &$category The lead's related category, if any.
	 * @return boolean Whether to proceed (TRUE) with sending the email or not (FALSE).
	 */
	protected function before_send_confirmation_email( &$category )
	{
		if ( ! $category || ! is_a($category, 'Charcoal_Base') || ! $category->v('send_confirmation_email') ) {
			// This category was configured to NOT send confirmation email
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
	 * Triggered after sending the confirmation email to the lead.
	 *
	 * This event receives the success/failure response from sending the email.
	 *
	 * @param  boolean $is_sent Whether the email was sent (TRUE) or not (FALSE).
	 * @return boolean Whether the email was sent (TRUE) or not (FALSE).
	 */
	protected function after_send_confirmation_email( $is_sent )
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
	 * Create the email entry for the lead.
	 *
	 * @return Charcoal_Email
	 */
	protected function create_confirmation_email_object()
	{
		$category = $this->category();

		$email     = new Charcoal_Email;
		$email->to = $this->get_lead_email_address();

		if ( $category ) {
			$email->subject = $category->p('confirmation_email_subject')->text();
			$email->from    = $category->p('confirmation_email_from')->text();
			$email->cc      = $category->v('confirmation_email_cc');
			$email->bcc     = $category->v('confirmation_email_bcc');
		}

		return $email;
	}

	/**
	 * Build the confirmation email template.
	 *
	 * @param  CMS_Contact_Category|null &$category The lead's related category, if any.
	 * @return Charcoal_Template_Controller
	 */
	protected function build_confirmation_email_template( &$category )
	{
		return Charcoal_Template::get( $category->v('confirmation_email_template') );
	}

	/**
	 * Render the confirmation email template.
	 *
	 * @param  CMS_Contact_Category|null &$category The lead's related category, if any.
	 * @return Charcoal_Template_Controller
	 */
	protected function render_confirmation_email_template( &$category )
	{
		$tpl = $this->build_confirmation_email_template( $category );
		$tpl->controller()->set_context( $this );

		$template = $tpl->render();
	}

	/**
	 * Filter the lead's email address field.
	 *
	 * @return string|array
	 */
	protected function get_lead_email_address()
	{
		$name_first = $this->p('name_first');
		$name_last  = $this->p('name_last');
		$name_full  = $this->p('name');

		$arr = [
			'email' => $this->p('email')->text()
		];

		if ( $name_first && $name_last ) {
			$arr['name'] = sprintf(
				'%1$s %2$s',
				$name_first->text(),
				$name_last->text()
			);
		} elseif ( $name_full ) {
			$arr['name'] = $name_full->text();
		}

		return email_from_array($arr);
	}

	/**
	 * Get the replacement array for the confirmation email
	 *
	 * This was put into its own method so subclasses of CMS_Contact_Entry
	 * can only redeclare this function and not the entire sender method.
	 *
	 * @return array The replacements in `key => value` pairs
	 */
	protected function _confirmation_email_replacements()
	{
		$category = $this->p('category')->as_object();

		$replacements = array_merge(
			$this->_user_metadata_email_replacements(),
			[
				'id'               => $this->id(),
				'email'            => $this->p('email')->text(),
				'telephone'        => $this->p('telephone')->text(),
				'name'             => $this->p('name')->text(),

				'organization'     => $this->p('organization')->text(),

				'message'          => $this->p('message')->text(),
				'subject'          => $this->p('subject')->text(),

				'category_name'    => $category->p('name')->text(),
				'category_subject' => $category->p('confirmation_email_subject')->text(),

				'base_url'         => Charcoal::$config['URL'],
				'lang'             => _l()
			]
		);

		return $replacements;
	}
}

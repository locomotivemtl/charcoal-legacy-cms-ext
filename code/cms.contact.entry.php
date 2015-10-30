<?php

/**
 * File: Contact Entry Class
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Mathieu Ducharme <mat@locomotive.ca>
 * @author     Stephen Begay <stephen@locomotive.ca>
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2012-09-29
 */

use \Charcoal as Charcoal;

/**
 * Class: Contact Entry
 *
 * Replaces Legacy's {@link //github.com/locomotivemtl/charcoal-legacy/blob/master/modules/cms/code/cms.contact.php `CMS_Contact`}
 *
 * Difference from Legacy's implementation:
 *
 * - Uses custom `Core_Trait_User_*` Traits with Charcoal Properties.
 *
 * @package CMS\Objects
 */
class CMS_Contact_Entry extends Charcoal_Object
{
	use // CMS_Trait_User_Stats,
		Core_Trait_User_Identity,
		Core_Trait_User_Organization,
		Core_Trait_User_Metadata;

	/**
	 * Entry Category
	 *
	 * @var int|ident|CMS_Contact_Category
	 * @see Property_Object
	 */
	public $category;

	/**
	 * User's / Entry Subject
	 *
	 * @var string
	 * @see Property_String
	 */
	public $subject;

	/**
	 * User's Message
	 *
	 * @var string
	 * @see Property_Text
	 */
	public $message;

	/**
	 * {@inheritdoc}
	 */
	protected function pre_save( $properties = null )
	{
		$this->_pre_save_user_metadata();

		if ( $this->category ) {
			$category = Charcoal::obj('CMS_Contact_Category');
			$category->load($this->category);

			if ( ! $category->id() ) {
				$this->category = null;
			}

			// Set a local var that will be used in post save
			/*if ( $category->send_confirmation_email && $category->confirmation_email_template ) {
				$this->confirmation_email_template = $category->confirmation_email_template;
			}*/
		}

		return parent::pre_save($properties);
	}

	/**
	 * {@inheritdoc}
	 *
	 * For Newsletter Subscriptions, this means sending the automatic
	 * confirmation email, if necessary.
	 */
	public function post_save( $properties = null )
	{
		$this->send_confirmation_email();
		$this->send_notification_email();

		return parent::post_save($properties);
	}

	/**
	 * Send an entry confirmation email to the inquirer.
	 *
	 * @return boolean TRUE if the email was successfully sent
	 */
	public function send_confirmation_email()
	{
		// If this entry is not associated to any category, there is not much one can do
		if ( ! $this->category ) {
			Charcoal::debug([
				'msg' => 'A contact entry object was saved without a category. Impossible to send confirmation email.'
			]);

			return false;
		}

		// Need the category object
		$category = $this->p('category')->as_object();

		if ( ! $category->v('send_confirmation_email') ) {
			// This category was configured to NOT send confirmation email
			return false;
		}

		$from     = $category->p('confirmation_email_from')->text();
		$subject  = $this->filter_subject( $category->p('confirmation_email_subject')->text(), 'confirmation');
		$template = Charcoal::email_template( $category->v('confirmation_email_template') );

		if (
			! $template ||
			! $subject
		) {
			return false;
		}

		// Cache currently active language,
		// to be restored at the end.
		$current_lang = _l();

		// Set Charcoal to user's language preference
		$l = Charcoal_L10n::get();
		$l->set_lang( $this->user_lang );

		$email           = new Charcoal_Email;
		$email->to       = [ $this->v('email') ];
		$email->subject  = $subject;
		$email->msg_html = $template;
		$email->from     = $from;

		$email->cc  = $category->v('confirmation_email_cc');
		$email->bcc = $category->v('confirmation_email_bcc');

		$email->replacements = $this->_confirmation_email_replacements();

		// Send email
		$response = $email->send();

		// Restore original language settings
		$l->set_lang( $current_lang );

		return $response;
	}

	/**
	 * Send a entry notification email to the administrators.
	 *
	 * @return boolean TRUE if the email was successfully sent
	 */
	public function send_notification_email()
	{
	}

	/**
	 * Alter the confirmation email subject.
	 *
	 * @param string $subject The string to alter.
	 * @param string $context Optional. How to filter the field.
	 *
	 * @return string
	 */
	public function filter_subject( $subject, $context = '' )
	{
		return $subject;
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
				'name_first'       => $this->p('name_first')->text(),
				'name_last'        => $this->p('name_last')->text(),

				'organization'     => $this->p('organization')->text(),
				'title'            => $this->p('title')->text(),

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

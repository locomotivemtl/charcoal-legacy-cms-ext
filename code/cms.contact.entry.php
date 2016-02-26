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
	use Core_Trait_User_Identity,
		Core_Trait_User_Organization,
		// CMS_Trait_User_Stats,
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
	 * Entry Category
	 *
	 * @var CMS_Contact_Category
	 */
	protected $_category;

	/**
	 * Store the currently active language; to be restired at the end.
	 *
	 * @var string
	 */
	protected $_current_lang;

	/**
	 * {@inheritdoc}
	 */
	protected function pre_save( $properties = null )
	{
		$this->_pre_save_user_metadata();

		/*if ( $category = $this->category() ) {
			// Set a local var that will be used in post save
			if ( $category->send_confirmation_email && $category->confirmation_email_template ) {
				$this->confirmation_email_template = $category->confirmation_email_template;
			}
		}*/

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
		if ( $this instanceof CMS_Interface_Email_Confirmation ) {
			$this->send_confirmation_email();
		}

		if ( $this instanceof CMS_Interface_Email_Notification ) {
			$this->send_notification_email();
		}

		return parent::post_save($properties);
	}

	/**
	 * Retrieve the current category
	 *
	 * @return CMS_Contact_Category
	 */
	public function category()
	{
		if ( ! isset( $this->_category ) ) {
			$this->_category = $this->p('category')->as_object();

			if ( ! $this->_category->id() ) {
				$this->_category = null;
			}
		}

		return $this->_category;
	}
}

<?php

/**
 * File: Sendable Confirmation Email Interface
 *
 * @copyright 2016 Locomotive
 * @license   PROPRIETARY
 * @link      http://charcoal.locomotive.ca
 * @author    Chauncey McAskill <chauncey@locomotive.ca>
 * @since     Version 2016-02-26
 */

/**
 * Interface: Sendable Confirmation Email
 *
 * @package CMS\Objects
 */
interface CMS_Interface_Email_Confirmation
{
	/**
	 * Send an entry confirmation email to the lead.
	 *
	 * @return boolean TRUE if the email was successfully sent
	 */
	public function send_confirmation_email();
}

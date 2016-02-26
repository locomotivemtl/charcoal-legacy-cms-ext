<?php

/**
 * File: Sendable Notification Email Interface
 *
 * @copyright 2016 Locomotive
 * @license   PROPRIETARY
 * @link      http://charcoal.locomotive.ca
 * @author    Chauncey McAskill <chauncey@locomotive.ca>
 * @since     Version 2016-02-26
 */

/**
 * Interface: Sendable Notification Email
 *
 * @package CMS\Objects
 */
interface CMS_Interface_Email_Notification
{
	/**
	 * Send an entry notification email to the administrators.
	 *
	 * @return boolean TRUE if the email was successfully sent
	 */
	public function send_notification_email();
}

<?php

/**
 * Action: Reload Template Options
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2015-09-07
 *
 * @package    CMS\Actions
 */

// ==========================================================================
// Setup
// ==========================================================================

$success     = false;
$redirect_to = null;
$response    = [
	'token' => CMS_Section::get_token()
];



// Required Parameters
// ==========================================================================

$token    = filter_input(INPUT_POST, 'nonce',    FILTER_SANITIZE_STRING);
$template = filter_input(INPUT_POST, 'template', FILTER_SANITIZE_STRING);



// Validate Nonce Token
// ==========================================================================

if ( ! Charcoal::token_validate( $token, sprintf( CMS_Section::NONCE_TOKEN, $template ) ) ) {
	$response['message'] = _t('session-expired');
}
else {

// Validate Template
// ==========================================================================

	$template_obj = Charcoal::obj('CMS_Template')->load( $template );

	if ( ! $template_obj->id() ) {
		$response['message'] = _t('template-missing');
	}
	else {

// Attempt Property Importing
// ==========================================================================

	}

}

// ==========================================================================
// Output
// ==========================================================================

CMS_Module::resolve_response($success, $response);

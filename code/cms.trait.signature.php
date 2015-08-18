<?php

/**
 * File: Agency Signature Trait
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Mathieu Ducharme <mat@locomotive.ca>
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2014-10-09
 */

namespace CMS;

/**
 * Trait: Agency Signature
 *
 * @package CMS\Objects
 */
trait Trait_Signature
{
    /**
     * For metadata usage, represents advisory information
     * related to the signature.
     *
     * @var string $signature_title
     * @see Property_Text
     */
    public $signature_title;

    /**
     * Publicly displayed content of the signature.
     *
     * @var string $signature_text
     * @see Property_Text
     */
    public $signature_text;

    /**
     * URL to the signature defining a hypertext link.
     *
     * @var string $signature_url
     * @see Property_Url
     */
    public $signature_url;

    /**
     * Render the agency's signature.
     *
     * @return string
     */
    public function made_by()
    {
        $replacements = [ 'obj' => $this ];

        $tpl = \Charcoal_Template::get( 'signature', $replacements );
        $tpl->set_template('<a target="_blank" href="{{obj.p.signature_url}}">{{&obj.p.signature_text}}</a>');

        return $tpl->render();
    }
}

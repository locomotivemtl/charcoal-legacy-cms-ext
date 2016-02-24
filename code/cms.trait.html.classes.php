<?php

/**
 * File: HTML Document Helpers Trait
 *
 * @copyright  2016 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2015-05-04
 */

namespace CMS;

use \Charcoal as Charcoal;
use \McAskill\TokenList\DOMClassList;

/**
 * HTML Document Helpers
 *
 * @package CMS\Objects
 */
trait Trait_HTML_Classes
{
    /**
     * The HTML "class" attribute for the `<body>` element.
     *
     * @var DOMClassList
     */
    protected $body_class;

    /**
     * The HTML "class" attribute for the `<html>` element.
     *
     * @var DOMClassList
     */
    protected $root_class;

    /**
     * Retrieve the HTML "class" attribute for the `<body>` element.
     *
     * @return DOMClassList
     */
    public function body_class()
    {
        if (!isset($this->body_class) || !($this->body_class instanceof DOMClassList)) {
            $this->body_class = new DOMClassList('body', ((array)$this->body_class ?: []));
        }

        return $this->body_class;
    }

    /**
     * Retrieve the the HTML "class" attribute for the `<html>` element.
     *
     * @return DOMClassList
     */
    public function root_class()
    {
        if (!isset($this->root_class) || !($this->root_class instanceof DOMClassList)) {
            $this->root_class = new DOMClassList('html', ((array)$this->root_class ?: []));
        }

        return $this->root_class;
    }

    /**
     * Retrieve the current character encoding.
     *
     * @return string A character encoding identifier.
     */
    public function charset()
    {
        return ini_get('default_charset');
    }
}

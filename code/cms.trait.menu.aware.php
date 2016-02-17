<?php

/**
 * File: CMS Menu Aware Object Trait
 *
 * @copyright  2016 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2016-02-16
 */

namespace CMS;

use \Charcoal as Charcoal;

/**
 * Trait: CMS Menu Aware Object
 *
 * @package CMS\Objects
 */
trait Trait_Menu_Aware
{
	/**
	 * Label to use when displaying a menu.
	 *
	 * @var string
	 * @see Property_String (l10n)
	 */
	public $menu_label;

	/**
	 * Locations this object appears in.
	 *
	 * @var string[]
	 * @see Property_Object|Property_Choice (l10n)
	 */
	public $menu_location;

	/**
	 * Retrieve the object's menu label.
	 *
	 * If none set, use the object's title.
	 *
	 * @return string
	 */
	public function menu_label()
	{
		$label = $this->p('menu_label')->text();
		$label = $this->render($label);

		if ( ! $label && $p = $this->p('title') ) {
			$label = $p->text();
		}

		if ( ! $label && $p = $this->p('name') ) {
			$label = $p->text();
		}

		return $label;
	}
}

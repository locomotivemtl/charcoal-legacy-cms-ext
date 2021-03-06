<?php

/**
 * File: CMS Section Class
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Mathieu Ducharme <mat@locomotive.ca>
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2015-08-18
 */

// Depdencies from Core Legacy
use \Charcoal\Trait_Category;
use \Charcoal\Trait_Category_Item;
use \Charcoal\Trait_Hierarchy;

// Local Depdencies
use \CMS\Trait_Menu_Aware;

/**
 * Class: CMS Section
 *
 * This object represent's a "Page" content type. Pages represent information that
 * isn't chronological—like a blog post or a news article—and is often hierarchically
 * organized.  Pages are for content such as "About", "Contact", "Legal Information",
 * etc. Pages can use different page templates to display more complex content.
 *
 * Replaces Legacy's {@link //github.com/locomotivemtl/charcoal-legacy/blob/master/modules/cms/code/cms.section.php `CMS_Section`}
 *
 * Differences from Legacy's implementation:
 *
 * - Uses {@see Trait_URL} instead of Core_Object_URL. The former is a modernized
 *   approach to the latter breaking up its functionality into smaller parts.
 * - Uses the {@link //github.com/locomotivemtl/charcoal-legacy-cms-meta CMS Metadata extension}
 *   as an example of how the incomplete {@see Trait_Meta} could be done. The metadata properties
 *   of the original CMS_Section have been removed (now provided by the third-party traits).
 * - Deprecates unused properties ("menu", "section_type", etc.).
 *
 * ## Inheritence
 *
 * #### Charcoal_Object
 *
 * Charcoal Properties:
 *
 * - $id
 * - $active
 *
 * #### Trait_URL_Slug
 *
 * Charcoal Properties:
 *
 * - $slug
 *
 * #### Trait_Menu_Aware
 *
 * Charcoal Properties:
 *
 * - $menu_label
 * - $menu_location
 *
 * #### Trait_Hierarchy
 *
 * Charcoal Properties:
 *
 * - $master
 * - $position
 *
 * #### CMS_Trait_Template
 *
 * Charcoal Properties:
 *
 * - $template
 * - $template_options
 *
 * #### Trait_Category_item
 *
 * Charcoal Properties:
 *
 * - $category (disabled)
 *
 * #### \CMS\Trait_Content_Metadata_Basic
 *
 * Charcoal Properties:
 *
 * - $meta_title
 * - $meta_description
 * - $meta_keywords
 *
 * #### \CMS\Trait_Content_Metadata_OpenGraph
 *
 * Charcoal Properties:
 *
 * - $meta_type
 * - $meta_image
 *
 * @package CMS\Objects
 */
class CMS_Section extends CMS_Object_URI {
	use Trait_Category,
		Trait_Category_Item,
		Trait_Hierarchy,
		Trait_Menu_Aware,
		CMS_Trait_Template;

// Properties
// ==========================================================================

	/**
	 * ...
	 *
	 * @var string
	 * @see Property_String
	 */
	public $ident;

	/**
	 * ...
	 *
	 * @var string
	 * @see Property_URL (l10n)
	 */
	public $external_url;

	/**
	 * ...
	 *
	 * @var string
	 * @see Property_Choice
	 */
	public $section_type;

	/**
	 * ...
	 *
	 * @var string[]
	 * @see Property_Object
	 */
	public $category;

	/**
	 * ...
	 *
	 * @var string
	 * @see Property_String (l10n)
	 */
	public $title;

	/**
	 * ...
	 *
	 * @var string
	 * @see Property_String (l10n)
	 */
	public $subtitle;

	/**
	 * ...
	 *
	 * @var string
	 * @see Property_HTML (l10n)
	 */
	public $summary;

	/**
	 * ...
	 *
	 * @var string
	 * @see Property_HTML (l10n)
	 */
	public $content;

	/**
	 * ...
	 *
	 * @var string[]
	 * @see Property_Image
	 */
	public $images;

	/**
	 * ...
	 *
	 * @var string[]
	 * @see Property_Image
	 */
	public $thumbnails;

	/**
	 * ...
	 *
	 * @var string[]
	 * @see Property_File (l10n)
	 */
	public $documents;



// Methods
// ==========================================================================

	/**
	 * Check wether this section is a parent (at any level) of a section
	 *
	 * @param mixed $section The object or ID to look for
	 *
	 * @return boolean
	 *
	 * @see Charcoal\Trait_Hierarchy::is_master_of()
	 *
	 * @todo Split this method into `is_parent_of()` (direct parent)
	 *       and `is_ancestor_of()` (any level).
	 */
	public function is_parent_of( $section )
	{
		if ( is_object($section) ) {
			$section_id = $section->id();
		}
		else {
			$section_id = $section;
		}

		while ( $section_id ) {
			$s = Charcoal::obj('CMS_Section')->load($section_id);

			if ( $s->master == $this->id() ) {
				return true;
			}

			$section_id = $s->master;
		}

		// If here, this section is not a parent of the parameter
		return false;
	}

	/**
	 * Check wether this section is a child (at any level) of a section
	 *
	 * @param mixed $section The object or the section id to look for
	 *
	 * @return boolean
	 *
	 * @see Charcoal\Trait_Hierarchy::is_children_of()
	 *
	 * @todo Needs implementation
	 * @todo Split this method into `is_child_of()` (direct children)
	 *       and `is_descendant_of()` (any level).
	 */
	public function is_child_of( $section )
	{
		if ( is_object($section) ) {
			$section_id = $section->id();
		}
		else {
			$section_id = $section;
		}

		return false;
	}

	/**
	 * Compute the unique "ident" property
	 *
	 * The $ident property will be generated from the $title if no value is provided.
	 *
	 * @used-by $this::pre_save()
	 * @used-by $this::pre_update()
	 *
	 * @see CMS_Trait_Url_Slug::filter_unique_slug()
	 *
	 * @param string $context Either 'update' or (by default) 'save'.
	 */
	protected function filter_unique_ident( $context = null )
	{
		if ( ! in_array( $context, [ 'save', 'update' ] ) ) {
			$context = 'save';
		}

		// Since Charcoal doesn't provide a copy of the previous data,
		// import the existing data and do some comparisons.
		if ( 'update' === $context ) {
			$old = Charcoal::obj( get_class($this) )->load( $this->id() );
		}
		else {
			$old = false;
		}

		if ( ! $old || ( ( $old instanceof Charcoal_Object ) && ( $old->ident !== $this->ident ) ) ) {
			$this->ident = generate_unique_object_ident( $this->p('ident') );
		}
	}



// Methods: Charcoal\Trait_URL
// ==========================================================================

	/**
	 * {@inheritdoc}
	 */
	public function url( $lang = '', $options = null)
	{
		if ( empty( $lang ) ) {
			$lang = _l();
		}

		$config = CMS_Config::get_latest();

		if ( $this->id() === $config->v('default_section') ) {
			return $lang . '/';
		}

		return parent::url( $lang, $options );
	}



// Methods: Charcoal_Object
// ==========================================================================

	/**
	 * {@inheritdoc}
	 */
	protected function pre_save( $properties = null )
	{
		$this->filter_unique_ident('save');

		return parent::pre_save($properties);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function pre_update()
	{
		$this->filter_unique_ident('update');

		return parent::pre_update();
	}

	/**
	 * Retrieve the excerpt of the object.
	 *
	 * This is either a user-supplied "summary", that is returned unchanged,
	 * or an automatically generated word-counted trimmed-down version of the
	 * full "description".
	 *
	 * @return string
	 */
	public function get_excerpt()
	{
		return trim_words( $this->p('summary')->text() ?: $this->p('content')->text() );
	}



// Methods: CMS\Trait_Content_Metadata_* (charcoal-legacy-cms-meta)
// ==========================================================================

	/**
	 * Retrieve the object's title—as it should appear
	 * in the graph—for the "og:title" meta-property.
	 *
	 * @see \CMS\Interface_Content_Metadata_Basic
	 *
	 * @return string
	 */
	public function meta_title()
	{
		return ( $this->p('meta_title')->text() ?: $this->p('title')->text() );
	}

	/**
	 * Retrieve the document's description.
	 *
	 * @see \CMS\Interface_Content_Metadata_Basic
	 *
	 * @return string
	 */
	public function meta_description()
	{
		return trim_words( $this->p('meta_description')->text() ?: $this->p('summary')->text() );
	}
}

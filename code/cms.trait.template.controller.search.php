<?php

/**
 * File: CMS Search Results Trait
 *
 * @copyright  2015 Locomotive
 * @license    PROPRIETARY
 * @link       http://charcoal.locomotive.ca
 * @author     Mathieu Ducharme <mat@locomotive.ca>
 * @author     Chauncey McAskill <chauncey@locomotive.ca>
 * @since      Version 2015-08-06
 */

/**
 * Trait: Search Results
 *
 * The CMS module's template controller for content search.
 * All template controllers should inherit this trait.
 *
 * @package CMS\Objects
 * @todo    McAskill [2015-09-16] Implement support for $_exact.
 * @todo    McAskill [2015-09-16] Implement support for $_sentence.
 * @todo    McAskill [2015-09-16] Implement support for $_stopwords.
 */
trait CMS_Trait_Template_Controller_Search
{
	/**
	 * Search Event Type
	 *
	 * @var string
	 */
	protected $event_type;

	/**
	 * Search Results
	 *
	 * @var Charcoal_Object[]
	 */
	protected $_results;

	/**
	 * Search Keyword
	 *
	 * @var string
	 */
	protected $_query;

	/**
	 * Whether to search by exact keyword.
	 *
	 * For example, spaces before/after the term aren't trimmed.
	 *
	 * @var bool
	 */
	protected $_exact;

	/**
	 * Whether to search by phrase. Defaults to FALSE.
	 *
	 * @var bool
	 */
	protected $_sentence;

	/**
	 * Cached list of stopwords.
	 *
	 * @var string[]
	 */
	protected $_stopwords;

	/**
	 * {@inheritdoc}
	 */
	protected function resolve_request()
	{
		$this->set_query( filter_input(INPUT_GET, 's', FILTER_SANITIZE_STRING) );
		$this->set_exact( filter_input(INPUT_GET, 'exact', FILTER_SANITIZE_NUMBER_INT) );
		$this->set_sentence( filter_input(INPUT_GET, 'sentence', FILTER_SANITIZE_NUMBER_INT) );

		$this->event_type = 'search';

		$this->log_query();

		return $this;
	}

	/**
	 * Determine if the search should be by exact keyword.
	 *
	 * @param bool $state Optional. If NULL, will lookup default setting in project config. Defaults to FALSE.
	 *
	 * @return $this
	 */
	public function set_exact( $state = false )
	{
		if ( is_null( $state ) ) {
			if ( isset( Charcoal::$config['search']['exact'] ) ) {
				$state = Charcoal::$config['search']['exact'];
			}
		}

		$this->_exact = (bool) $state;

		return $this;
	}

	/**
	 * Determine if the search should be by phrase.
	 *
	 * @param bool $state Optional. If NULL, will lookup default setting in project config. Defaults to FALSE.
	 *
	 * @return $this
	 */
	public function set_sentence( $state = false )
	{
		if ( is_null( $state ) ) {
			if ( isset( Charcoal::$config['search']['sentence'] ) ) {
				$state = Charcoal::$config['search']['sentence'];
			}
		}

		$this->_sentence = (bool) $state;

		return $this;
	}

	/**
	 * Set the contents of the search query variable
	 *
	 * @param string $query
	 *
	 * @return $this
	 */
	public function set_query( $query )
	{
		// Allow search queries to be very long
		if ( ! is_scalar( $query ) || ( ! empty( $query ) && strlen( $query ) > 1200 ) ) {
			/** @todo Throw or Log Exception? */
			$query = '';
		}

		$this->_query = $query;

		return $this;
	}

	/**
	 * Retrieve the contents of the search query variable
	 *
	 * @param bool $escaped Whether the result is escaped for HTML attributes.
	 *
	 * @return string
	 */
	public function get_query( $escaped = true )
	{
		$query = $this->_query;

		if ( $escaped ) {
			if ( is_array( $query ) ) {
				foreach ( $query as &$q ) {
					$q = htmlspecialchars( $q, ENT_QUOTES, ini_get('default_charset'), false );
				}
			}
			else {
				$query = htmlspecialchars( $query, ENT_QUOTES, ini_get('default_charset'), false );
			}
		}

		return $query;
	}

	/**
	 * Determine if the search query variable has contents.
	 *
	 * @return bool
	 */
	public function has_query()
	{
		return ! empty( $this->get_query() );
	}

	/**
	 * Alias of self::get_query()
	 */
	public function keyword()
	{
		return $this->get_query();
	}

	/**
	 * Perform Search
	 *
	 * @return array
	 */
	public function get_results()
	{
		if ( ! isset( $this->_results ) ) {
			$query = $this->get_query();

			$this->_results = [];

			if ( $query && ! empty( Charcoal::$config['search']['objects'] ) ) {
				$objs = Charcoal::$config['search']['objects'];

				$is_assoc = is_assoc( $objs );

				foreach ( $objs as $key => $val ) {
					if ( $is_assoc ) {
						$obj_type = $key;
						$obj_data = $val;
					}
					else {
						$obj_type = $val;
						$obj_data = [];
					}

					if ( isset( $obj_data['active'] ) && ! $obj_data['active'] ) {
						continue;
					}

					if ( ! isset( $obj_data['name'] ) ) {
						$obj_data['name'] = $obj_type;
					}

					if ( ! isset( $obj_data['method'] ) ) {
						$obj_data['method'] = 'results_from_search';
					}

					$callback = [ Charcoal::obj( $obj_type ), $obj_data['method'] ];

					if ( is_callable( $callback ) ) {
						$this->_results[ $obj_data['name'] ] = call_user_func( $callback, $query );
					}
					elseif ( isset( $obj_data['options'] ) ) {
						$this->_results[ $obj_data['name'] ] = $this->__results_from_search( $obj_type, $obj_data['options'], $query );
					}
				}
			}
		}

		return $this->_results;
	}

	/**
	 * Retrieve the number of found results.
	 *
	 * @return int
	 */
	public function total_results()
	{
		$results = $this->get_results();
		$total   = 0;

		foreach	( $results as $set ) {
			$total = $total + count( $set );
		}

		return $total;
	}

	/**
	 * Determine if results have been found.
	 *
	 * @return bool
	 */
	public function has_results()
	{
		return (bool) $this->total_results();
	}

	/**
	 * Retrieve the objects based on the search query.
	 *
	 * A generic search query if a custom search method, such as `results_from_search()`,
	 * isn't specified.
	 *
	 * @param string|Charcoal_Object $obj_type     A prototype of the desired content type to search.
	 * @param mixed[]                $options      Options, such as listing the Charcoal Properties (database columns) to search.
	 * @param string|string[]        $search_terms One or more search terms.
	 *
	 * @return Charcoal_Object[]|Charcoal_List
	 */
	protected static function __results_from_search( $obj_type, $options, $search_terms )
	{
		if ( ! $search_terms ) {
			return [];
		}

		if ( $obj_type instanceof Charcoal_Object ) {
			$proto    = $obj_type;
			$obj_type = $proto->obj_type();
		}
		else {
			$proto = Charcoal::obj( $obj_type );
		}

		$l = _l();

		if ( isset( $options['select'] ) ) {
			$select = implode( ', ', $options['select'] );
		}
		else {
			$select = '*';
		}

		if ( isset( $options['fulltext'] ) ) {
			$match = [];

			foreach ( $options['fulltext'] as $p ) {
				$prop = $proto->p( $p );

				if ( $prop ) {
					$match[ $p ] = ( $prop->l10n() ? "`{$p}_{$l}`" : "`{$p}`" );
				}
			}

			$match = implode( ', ', $match );

			if ( $match ) {
				$match = 'MATCH(' . $match . ') AGAINST(:s1 IN NATURAL LANGUAGE MODE)';
			}
		}
		else {
			$match = '';
		}

		if ( isset( $options['like'] ) ) {
			$like = [];

			foreach ( $options['like'] as $p ) {
				$prop = $proto->p( $p );

				if ( $prop ) {
					$like[ $p ] = '`' . $p . ( $prop->l10n() ? '_' . $l : '' ) . '` LIKE :s2';
				}
			}

			$like = implode( ' OR ', $like );
		}
		else {
			$like = '';
		}

		if ( ! $match && ! $like ) {
			return [];
		}

		$sql_query = '
			SELECT
				' . $select . ',
				' . $match . ' AS `relevance`
			FROM
				`' . $proto->table() . '`
			WHERE
				`active` = 1
			AND
				( ' . $match . ( $match && $like ? ' OR ' : '' ) . $like . ' )
			ORDER BY
				`relevance` DESC';

		return Charcoal::obj_list( $obj_type )->load_from_query( $sql_query, [
			':s1' => $search_terms,
			':s2' => '%' . htmlentities( $search_terms, ENT_COMPAT, 'UTF-8' ) . '%'
		] );
	}

	/**
	 * Retrieve stopwords to exclude when parsing search terms.
	 *
	 * @return array Stopwords.
	 */
	protected function get_stopwords()
	{
		if ( ! isset( $this->_stopwords ) ) {
			/**
			 * Comma-separated list of search stopwords in English and French.
			 * When translating into your language, provide commonly accepted stopwords
			 * in your language rather than translate the ones below.
			 */
			$words = explode( ',', _t('stopwords') );

			if ( preg_match( '#(\r|\n|\t|\s)#', _t('stopwords') ) ) {
				$this->_stopwords = [];
				foreach ( $words as $word ) {
					$word = trim( $word, "\r\n\t " );

					if ( $word ) {
						$this->_stopwords[] = $word;
					}
				}
			}
			else {
				$this->_stopwords = $words;
			}
		}

		return $this->_stopwords;
	}

	/**
	 * Log a search attempt
	 *
	 * @todo [2015-09-15] Move to a specialized log class at some point...
	 */
	public function log_query()
	{
		$query = $this->get_query();

		if ( ! $query ) {
			return;
		}

		if ( ! is_array( $query ) && ! is_object( $query ) ) {
			$query = [ $query ];
		}

		$log = new Charcoal_Log;
		$log->event_type = $this->event_type;
		$log->data = $query;
		$log->save();
	}
}

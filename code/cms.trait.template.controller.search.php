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
					$q = htmlspecialchars( $q, ENT_QUOTES );
				}
			}
			else {
				$query = htmlspecialchars( $query, ENT_QUOTES );
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

			$this->_results = new ArrayIterator;

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
			_a( 'stopwords', [
				'en' => "www,a,a's,able,about,above,according,accordingly,across,actually,after,afterwards,again,against,ain't,all,allow,allows,almost,alone,along,already,also,although,always,am,among,amongst,an,and,another,any,anybody,anyhow,anyone,anything,anyway,anyways,anywhere,apart,appear,appreciate,appropriate,are,aren't,around,as,aside,ask,asking,associated,at,available,away,awfully,b,be,became,because,become,becomes,becoming,been,before,beforehand,behind,being,believe,below,beside,besides,best,better,between,beyond,both,brief,but,by,c,c'mon,c's,came,can,can't,cannot,cant,cause,causes,certain,certainly,changes,clearly,co,com,come,comes,concerning,consequently,consider,considering,contain,containing,contains,corresponding,could,couldn't,course,currently,d,definitely,described,despite,did,didn't,different,do,does,doesn't,doing,don't,done,down,downwards,during,e,each,edu,eg,eight,either,else,elsewhere,enough,entirely,especially,et,etc,even,ever,every,everybody,everyone,everything,everywhere,ex,exactly,example,except,f,far,few,fifth,first,five,followed,following,follows,for,former,formerly,forth,four,from,further,furthermore,g,get,gets,getting,given,gives,go,goes,going,gone,got,gotten,greetings,h,had,hadn't,happens,hardly,has,hasn't,have,haven't,having,he,he's,hello,help,hence,her,here,here's,hereafter,hereby,herein,hereupon,hers,herself,hi,him,himself,his,hither,hopefully,how,howbeit,however,i,i'd,i'll,i'm,i've,ie,if,ignored,immediate,in,inasmuch,inc,indeed,indicate,indicated,indicates,inner,insofar,instead,into,inward,is,isn't,it,it'd,it'll,it's,its,itself,j,just,k,keep,keeps,kept,know,knows,known,l,last,lately,later,latter,latterly,least,less,lest,let,let's,like,liked,likely,little,look,looking,looks,ltd,m,mainly,many,may,maybe,me,mean,meanwhile,merely,might,more,moreover,most,mostly,much,must,my,myself,n,name,namely,nd,near,nearly,necessary,need,needs,neither,never,nevertheless,new,next,nine,no,nobody,non,none,noone,nor,normally,not,nothing,novel,now,nowhere,o,obviously,of,off,often,oh,ok,okay,old,on,once,one,ones,only,onto,or,other,others,otherwise,ought,our,ours,ourselves,out,outside,over,overall,own,p,particular,particularly,per,perhaps,placed,please,plus,possible,presumably,probably,provides,q,que,quite,qv,r,rather,rd,re,really,reasonably,regarding,regardless,regards,relatively,respectively,right,s,said,same,saw,say,saying,says,second,secondly,see,seeing,seem,seemed,seeming,seems,seen,self,selves,sensible,sent,serious,seriously,seven,several,shall,she,should,shouldn't,since,six,so,some,somebody,somehow,someone,something,sometime,sometimes,somewhat,somewhere,soon,sorry,specified,specify,specifying,still,sub,such,sup,sure,t,t's,take,taken,tell,tends,th,than,thank,thanks,thanx,that,that's,thats,the,their,theirs,them,themselves,then,thence,there,there's,thereafter,thereby,therefore,therein,theres,thereupon,these,they,they'd,they'll,they're,they've,think,third,this,thorough,thoroughly,those,though,three,through,throughout,thru,thus,to,together,too,took,toward,towards,tried,tries,truly,try,trying,twice,two,u,un,under,unfortunately,unless,unlikely,until,unto,up,upon,us,use,used,useful,uses,using,usually,uucp,v,value,various,very,via,viz,vs,w,want,wants,was,wasn't,way,we,we'd,we'll,we're,we've,welcome,well,went,were,weren't,what,what's,whatever,when,whence,whenever,where,where's,whereafter,whereas,whereby,wherein,whereupon,wherever,whether,which,while,whither,who,who's,whoever,whole,whom,whose,why,will,willing,wish,with,within,without,won't,wonder,would,would,wouldn't,x,y,yes,yet,you,you'd,you'll,you're,you've,your,yours,yourself,yourselves,z,zero",
				'fr' => "www,a,à,â,abord,afin,ah,ai,aie,ainsi,allaient,allo,allô,allons,après,assez,attendu,au,aucun,aucune,aujourd,aujourd'hui,auquel,aura,auront,aussi,autre,autres,aux,auxquelles,auxquels,avaient,avais,avait,avant,avec,avoir,ayant,b,bah,beaucoup,bien,bigre,boum,bravo,brrr,c,ça,car,ce,ceci,cela,celle,celle-ci,celle-là,celles,celles-ci,celles-là,celui,celui-ci,celui-là,cent,cependant,certain,certaine,certaines,certains,certes,ces,cet,cette,ceux,ceux-ci,ceux-là,chacun,chaque,cher,chère,chères,chers,chez,chiche,chut,ci,cinq,cinquantaine,cinquante,cinquantième,cinquième,clac,clic,combien,comme,comment,compris,concernant,contre,couic,crac,d,da,dans,de,debout,dedans,dehors,delà,depuis,derrière,des,dès,désormais,desquelles,desquels,dessous,dessus,deux,deuxième,deuxièmement,devant,devers,devra,différent,différente,différentes,différents,dire,divers,diverse,diverses,dix,dix-huit,dixième,dix-neuf,dix-sept,doit,doivent,donc,dont,douze,douzième,dring,du,duquel,durant,e,effet,eh,elle,elle-même,elles,elles-mêmes,en,encore,entre,envers,environ,es,ès,est,et,etant,étaient,étais,était,étant,etc,été,etre,être,eu,euh,eux,eux-mêmes,excepté,f,façon,fais,faisaient,faisant,fait,feront,fi,flac,floc,font,g,gens,h,ha,hé,hein,hélas,hem,hep,hi,ho,holà,hop,hormis,hors,hou,houp,hue,hui,huit,huitième,hum,hurrah,i,il,ils,importe,j,je,jusqu,jusque,k,l,la,là,laquelle,las,le,lequel,les,lès,lesquelles,lesquels,leur,leurs,longtemps,lorsque,lui,lui-même,m,ma,maint,mais,malgré,me,même,mêmes,merci,mes,mien,mienne,miennes,miens,mille,mince,moi,moi-même,moins,mon,moyennant,n,na,ne,néanmoins,neuf,neuvième,ni,nombreuses,nombreux,non,nos,notre,nôtre,nôtres,nous,nous-mêmes,nul,o,o|,ô,oh,ohé,olé,ollé,on,ont,onze,onzième,ore,ou,où,ouf,ouias,oust,ouste,outre,p,paf,pan,par,parmi,partant,particulier,particulière,particulièrement,pas,passé,pendant,personne,peu,peut,peuvent,peux,pff,pfft,pfut,pif,plein,plouf,plus,plusieurs,plutôt,pouah,pour,pourquoi,premier,première,premièrement,près,proche,psitt,puisque,q,qu,quand,quant,quanta,quant-à-soi,quarante,quatorze,quatre,quatre-vingt,quatrième,quatrièmement,que,quel,quelconque,quelle,quelles,quelque,quelques,quelqu'un,quels,qui,quiconque,quinze,quoi,quoique,r,revoici,revoilà,rien,s,sa,sacrebleu,sans,sapristi,sauf,se,seize,selon,sept,septième,sera,seront,ses,si,sien,sienne,siennes,siens,sinon,six,sixième,soi,soi-même,soit,soixante,son,sont,sous,stop,suis,suivant,sur,surtout,t,ta,tac,tant,te,té,tel,telle,tellement,telles,tels,tenant,tes,tic,tien,tienne,tiennes,tiens,toc,toi,toi-même,ton,touchant,toujours,tous,tout,toute,toutes,treize,trente,très,trois,troisième,troisièmement,trop,tsoin,tsouin,tu,u,un,une,unes,uns,v,va,vais,vas,vé,vers,via,vif,vifs,vingt,vivat,vive,vives,vlan,voici,voilà,vont,vos,votre,vôtre,vôtres,vous,vous-mêmes,vu,w,x,y,z,zut",
			] );

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

		$log = new Charcoal_Log;
		$log->event_type = 'search';
		$log->data = $query;
		$log->save();
	}
}

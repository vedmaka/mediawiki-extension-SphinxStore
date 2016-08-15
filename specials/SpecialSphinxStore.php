<?php
/**
 * HelloWorld SpecialPage for BoilerPlate extension
 *
 * @file
 * @ingroup Extensions
 */

class SpecialSphinxStore extends SpecialPage {
	public function __construct() {
		parent::__construct( 'SphinxStore' );
	}

	/**
	 * Show the page to the user
	 *
	 * @param string $sub The subpage string argument (if any).
	 *  [[Special:HelloWorld/subpage]].
	 */
	public function execute( $sub ) {
		$out = $this->getOutput();
		$out->setPageTitle('SphinxStore test page');

		// Sphinx API
		/*$sphinx = new SphinxClient();
		$sphinx->SetServer( "127.0.0.1", 9312 );
		$sphinx->SetArrayResult( true );

		//$sphinx->SetFilter("geonames_id", array(12345) );
		$result = $sphinx->Query( "sample" );

		if( $result ) {
			if( array_key_exists('matches', $result) && is_array($result['matches']) && count($result['matches']) ) {
				foreach ($result['matches'] as $match) {

					$out->addHTML("<br><b>".$match['attrs']['semantic_title']."</b>");

				}
			}
		}else{
			$out->addHTML( $sphinx->GetLastError() );
		}*/

		// Sphinx QL
		/*$conn = new Foolz\SphinxQL\Drivers\Mysqli\Connection();
		$conn->setParams( array('host' => '127.0.0.1', 'port' => 9313) );

		$query = Foolz\SphinxQL\SphinxQL::create( $conn )
			->select('*')
			->from('wiki_main')
			->match('', 'sample');

		$result = $query->execute();
		$results = array();

		foreach ($result as $row) {
			$results[] = $row;
		}*/
		
		error_reporting(E_ALL); ini_set('display_errors', 1);
		
		$conn = new Foolz\SphinxQL\Drivers\Mysqli\Connection();
		$conn->setParams( array('host' => '127.0.0.1', 'port' => 9313) );
		Foolz\SphinxQL\Helper::create($conn)->flushRtIndex('wiki_rt');
		
		$query = Foolz\SphinxQL\SphinxQL::create( $conn )
			->delete()->from('wiki_rt')->where('id','>',0)->execute();
			
		$out->addHTML('Cleared index.<br>');
		
		$jsonData = array(
				'property1' => 'hello',
				'property2' => 'world',
				'property3' => 1024
			);
		$json = json_encode($jsonData);
		
		$query = Foolz\SphinxQL\SphinxQL::create( $conn )
			->insert()->into('wiki_rt')->set(array(
					'id' => 1,
					'title' => 'Test',
					'content' => 'Test 12345',
					'properties' => $json
				));
				
		$result = $query->execute();
		
		$out->addHTML('Added rows.<br>');
		
		$out->addHTML('Making query ..<br>');
		
		$query = Foolz\SphinxQL\SphinxQL::create( $conn )
			->select('*')
			->from('wiki_rt')
			->match('', 'test')
			->where('properties.property1', 'world');
			
		$result = $query->execute();
		
		foreach( $result as $r ) {
			$out->addHTML('<br> - '. print_r($r, 1));
		}

	}

	protected function getGroupName() {
		return 'other';
	}
}

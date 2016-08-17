<?php

use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Drivers\Mysqli\Connection;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = dirname( __FILE__ ) . '/../../..';
}
require_once( "$IP/maintenance/Maintenance.php" );

class SphinxStoreSearchTestMaintenance extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addOption( "clear", "deletes everything from index", false, false );
		$this->addOption( "add", "adds sample document to index", false, false );
		$this->addOption( "search", "searches for provided term", false, true );
		$this->addOption( "query", "searches for provided term", false, true );
		$this->addOption( "touch", "test", false, false );
		$this->mDescription = "Test commands set for sphinx real-time indexes.";
	}
	public function execute() {

		$conn = new Foolz\SphinxQL\Drivers\Mysqli\Connection();
		$conn->setParams( array('host' => '127.0.0.1', 'port' => 9313) );
		$query = Foolz\SphinxQL\SphinxQL::create( $conn );

		if( $this->hasOption( 'clear' ) ) {
			$this->output( "Clearing index from documents..\n" );
			$query->delete()->from('wiki_rt')->where('id','>',0)->execute();
		}

		if( $this->hasOption( 'add' ) ) {
			$this->output( "Adding sample document to the index..\n" );
			$query->replace()->into('wiki_rt')->set(array(
				'id' => 1,
				'title' => 'Test',
				'page_title' => 'Test',
				'alias_title' => 'Test',
				'content' => 'Test 12345',
				'properties' => json_encode(array(
					'property1' => 'hello',
					'property2' => array( 'hello', 'world' ),
					'property3' => 1024
				))
			))->execute();
		}

		if( $this->hasOption( 'check' ) ) {
			$this->output( "Checking document exists in the index..\n" );

			$result = $query->select('*')->from('wiki_rt')
				->where('properties.property3', 1024)
				->execute();
			if( $result->count() ) {
				$this->output( "Found {$result->count()} documents:\n" );
				foreach ($result as $document) {
					$this->output( print_r( $document ,1)."\n\n" );
				}
			}else{
				$this->output( "Nothing found!\n" );
			}
		}

		if( $this->hasOption('query') ) {
			$queryText = $this->getOption('query');
			$this->output( "Querying: '{$queryText}'\n");
			$result = $query->query( $queryText )->execute();
			if( $result->count() ) {
				$this->output( "Found {$result->count()} documents:\n" );
				foreach ($result as $document) {
					$this->output( print_r( $document ,1)."\n\n" );
				}
			}else{
				$this->output( "Nothing found!\n" );
			}
		}

		if( $this->hasOption( 'search' ) ) {
			$this->output( "Searching for term..\n" );
			$term = $this->getOption( 'search' );
			$result = $query->select('*')->from('wiki_rt')->match('*', $term)->execute();
			if( $result->count() ) {
				$this->output( "Found {$result->count()} documents:\n" );
				foreach ($result as $document) {
					$this->output( print_r( $document ,1)."\n\n" );
				}
			}else{
				$this->output( "Nothing found!\n" );
			}
		}

		if( $this->hasOption( 'touch' ) ) {
			$this->output( "Mass store update..\n" );
			$dbw = wfGetDB(DB_SLAVE);
			$pages = $dbw->select('page','*',array('page_namespace' => 0, 'page_is_redirect' => 0));
			foreach ($pages as $page) {
				$this->output( "\tPage {$page->page_id}\n" );
			}
		}

		$this->output( "Finished!\n" );
	}
}

$maintClass = 'SphinxStoreSearchTestMaintenance'; // Tells it to run the class
require_once( RUN_MAINTENANCE_IF_MAIN );

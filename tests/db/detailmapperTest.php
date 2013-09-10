<?php
namespace OCA\Projects\Controller;

// use \OCA\AppFramework\Http\Request;
// use \OCA\AppFramework\Http\TemplateResponse;
// use \OCA\AppFramework\Http\RedirectResponse;
// use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Utility\MapperTestUtility;
use \OCA\Projects\Db\Detail;
use \OCA\Projects\Db\DetailMapper;
// use \OCA\Projects\Db\Project;
// use \OCA\Projects\Db\ProjectMapper;
require_once(__DIR__ . "/../classloader.php");

class DetailMapperTest extends MapperTestUtility {

	public function setUp(){
		$this->beforeEach();
		$this->mapper = new DetailMapper($this->api);
	}
	
	public function testMustHaveAtleastOneTest() {
		
	}

	 // Irrelevant?
	// public function testMapperShouldSetTableName() {
		// $this->assertEquals('*PREFIX*projects_details', $this->mapper->getTableName());
	// }

	 // Irrelevant?
	// public function testFindEntities(){
		// $sql = 'SELECT * FROM `*PREFIX*projects_details` WHERE `project_id = ?';
		// $rows = array(
			// array('detail_key' => 'hi')
		// );
		// $detail = new Detail();
		// $detail->setDetailKey('hi');
		// $detail->resetUpdatedFields();
		// $row = $this->setMapperResult($sql, array(), $rows);
		// $result = $this->mapper->findAllEntities($sql);
		// $this->assertEquals(array($detail), $result);
	// }


	 // Irrelevant?
	// public function testGetDetails(){
		// $sql = 'hi';
		// $params = array('jo');
		// $rows = array(
			// array('hi')
		// );
		// $row = $this->setMapperResult($sql, $params, $rows);
		// $this->mapper->findOneEntity($sql, $params);
	// }

}

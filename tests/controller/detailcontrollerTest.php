<?php
namespace OCA\Projects\Controller;

use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\TemplateResponse;
use \OCA\AppFramework\Http\RedirectResponse;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Utility\ControllerTestUtility;
use \OCA\Projects\Db\Detail;
use \OCA\Projects\Db\DetailMapper;
use \OCA\Projects\Db\Project;
use \OCA\Projects\Db\ProjectMapper;
use \OCA\Projects\Controller\ProjectController;
require_once(__DIR__ . "/../classloader.php");

class DetailControllerTest extends ControllerTestUtility {
	
	private $api;
	private $request;
	private $controller;
	private $projectController;

	public function setUp() {
		$this->api = $this->getAPIMock('OCA\Projects\Core\API');
		$this->api->expects($this->any())->method('getUserId')->will($this->returnValue('Foo'));
		$this->request = new Request(array('get'=>array('id'=>123)));
		$this->projectController = $this->getMock('ProjectController', array('getProject'));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetDetailNoProjectId() {
		$response = $this->controller->getDetail(null, 'Foo');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetDetailNoData_key() {
		$response = $this->controller->getDetail(123, null);
	}

	public function testGetDetailNoSuchDetail() {
		$mockDetailMapper = $this->getMock('DetailMapper', array('getDetail'));
		$mockDetailMapper->expects($this->once())->method('getDetail')->will($this->returnValue(null));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->getDetail(123, 'Foo');
		$this->assertNull($response);
	}

	public function testGetDetailOK() {
		$mockDetail = new Detail(array('data_key'=>'fish', 'data_value'=>'chips'));
		$mockDetailMapper = $this->getMock('DetailMapper', array('getDetail'));
		$mockDetailMapper->expects($this->once())->method('getDetail')->will($this->returnValue($mockDetail));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->getDetail(123, 'Foo');
		$this->assertEquals($mockDetail, $response);
	}
	
	public function testCanReadNullProject() {
		$this->assertNull( $this->controller->canRead(null) );
	}
	
	public function testCanReadUserProject() {
		$mockProject = array('id'=>123, 'uid'=>'Foo');
		$this->assertTrue( $this->controller->canRead($mockProject) );
	}

	public function testCanReadSharedProjectNoPerm() {
		$mockProject = array('id'=>123, 'uid'=>'Bar', 'permissions'=>array());
		$this->assertNull( $this->controller->canRead($mockProject) );
	}

	public function testCanReadSharedProjectWithPerm() {
		$mockProject = array('id'=>123, 'uid'=>'Bar', 'permissions'=>array('read'));
		$this->assertTrue( $this->controller->canRead($mockProject) );
	}
	
	public function testCanCreateNullProject() {
		$this->assertNull( $this->controller->canCreate(null) );
	}
	
	public function testCanCreateUserProject() {
		$mockProject = array('id'=>123, 'uid'=>'Foo');
		$this->assertTrue( $this->controller->canCreate($mockProject) );
	}

	public function testCanCreateSharedProjectNoPerm() {
		$mockProject = array('id'=>123, 'uid'=>'Bar', 'permissions'=>array());
		$this->assertNull( $this->controller->canCreate($mockProject) );
	}

	public function testCanCreateSharedProjectWithPerm() {
		$mockProject = array('id'=>123, 'uid'=>'Bar', 'permissions'=>array('create'));
		$this->assertTrue( $this->controller->canCreate($mockProject) );
	}
	
	public function testCanUpdateNullProject() {
		$this->assertNull( $this->controller->canUpdate(null) );
	}
	
	public function testCanUpdateUserProject() {
		$mockProject = array('id'=>123, 'uid'=>'Foo');
		$this->assertTrue( $this->controller->canUpdate($mockProject) );
	}

	public function testCanUpdateSharedProjectNoPerm() {
		$mockProject = array('id'=>123, 'uid'=>'Bar', 'permissions'=>array());
		$this->assertNull( $this->controller->canUpdate($mockProject) );
	}

	public function testCanUpdateSharedProjectWithPerm() {
		$mockProject = array('id'=>123, 'uid'=>'Bar', 'permissions'=>array('update'));
		$this->assertTrue( $this->controller->canUpdate($mockProject) );
	}
	
	public function testCanDeleteNullProject() {
		$this->assertNull( $this->controller->canDelete(null) );
	}
	
	public function testCanDeleteUserProject() {
		$mockProject = array('id'=>123, 'uid'=>'Foo');
		$this->assertTrue( $this->controller->canDelete($mockProject) );
	}

	public function testCanDeleteSharedProjectNoPerm() {
		$mockProject = array('id'=>123, 'uid'=>'Bar', 'permissions'=>array());
		$this->assertNull( $this->controller->canDelete($mockProject) );
	}

	public function testCanDeleteSharedProjectWithPerm() {
		$mockProject = array('id'=>123, 'uid'=>'Bar', 'permissions'=>array('delete'));
		$this->assertTrue( $this->controller->canDelete($mockProject) );
	}
	
	public function testIndexAnnotations(){
		$this->assertAnnotations($this->controller, 'index', array('IsAdminExemption', 'IsSubAdminExemption', 'CSRFExemption'));
	}

	public function testIndexNoProject() {
		$mockDetailMapper = $this->getMock('DetailMapper', array('getDetails'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\JSONResponse', $response);
		$this->assertEquals('403', $response->getStatus());
	}
	
	public function testIndexUserProject() {
		$mockDetails = array( new Detail(array('id'=>1, 'projectId'=>123, 'dataKey'=>'fish', 'dataValue'=>'chips')));
		$mockDetailMapper = $this->getMock('DetailMapper', array('getDetails'));
		$mockDetailMapper->expects($this->once())->method('getDetails')->will($this->returnValue($mockDetails));
		$mockProject = array('id'=>123, 'uid'=>'Foo');
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->index();
		// $this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		// $this->assertEquals('detail/index', $response->getTemplateName());
	}
	
	public function testIndexSharedProjectNoReadPerm() {
		$mockDetailMapper = $this->getMock('DetailMapper', array('getDetails'));
		$mockProject = new Project(array('id'=>123, 'uid'=>'bar', 'permissions'=>array()));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\JSONResponse', $response);
		$this->assertEquals('403', $response->getStatus());
	}
	
	public function testIndexSharedProjectWithReadPerm() {
		$mockDetails = array( new Detail(array('id'=>1, 'projectId'=>123, 'dataKey'=>'fish', 'dataValue'=>'chips')));
		$mockDetailMapper = $this->getMock('DetailMapper', array('getDetails'));
		$mockDetailMapper->expects($this->once())->method('getDetails')->will($this->returnValue($mockDetails));
		$mockProject = new Project(array('id'=>123, 'uid'=>'bar', 'permissions'=>array('read')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('detail/index', $response->getTemplateName());
	}

	public function testShowAnnotations() {
		$this->assertAnnotations($this->controller, 'show', array('IsAdminExemption', 'IsSubAdminExemption', 'CSRFExemption'));
	}

	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Detail Key not set
	 */
	public function testShowNoDetailKeyInRequest() {
		$response= $this->controller->show();
	}

	public function testShowNoProject() {
		$this->request = new Request(array('get'=>array('id'=>123, 'detailKey'=>'Name')));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->show();
		$this->assertInstanceOf('\OCA\AppFramework\Http\JSONResponse', $response);
		$this->assertEquals('403', $response->getStatus());
	}
	
	public function testShowNoSuchDetailForProject() {
		$mockDetailMapper = $this->getMock('DetailMapper', array('getDetail'));
		$mockDetailMapper->expects($this->once())->method('getDetail')->will($this->returnValue(null));
		$this->request = new Request(array('get'=>array('id'=>123, 'detailKey'=>'Name')));
		$mockProject = new Project(array('id'=>123, 'uid'=>'Foo'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->show();
		$this->assertInstanceOf('\OCA\AppFramework\Http\JSONResponse', $response);
		$this->assertEquals('403', $response->getStatus());
	}
	
	public function testShowDetailForProject() {
		$mockDetail = $this->getMock('Detail');
		$mockDetailMapper = $this->getMock('DetailMapper', array('getDetail'));
		$mockDetailMapper->expects($this->once())->method('getDetail')->will($this->returnValue($mockDetail));
		$this->request = new Request(array('get'=>array('id'=>123, 'detailKey'=>'Name')));
		$mockProject = array('id'=>123,'uid'=>'Foo');
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->show();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('detail/show', $response->getTemplateName());
	}

	public function testCreateAnnotations() {
		$this->assertAnnotations($this->controller, 'create', array('IsAdminExemption', 'IsSubAdminExemption'));
	}
	
	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Project Id not set
	 */
	public function testCreateDetailNoProject() {
		$this->controller->create();
	}

	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Detail Key not set
	 */
	public function testCreateDetailNoDetail_key() {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue(123));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$this->controller->create();
	}

	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Detail Value not set
	 */
	public function testCreateDetailNoDetail_value() {
		$this->request = new Request(array('post'=>array('id'=>123, 'detailKey'=>'Fish')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue(123));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$this->controller->create();
	}

	public function testCreateDetailNoProjectCreatePerm() {
		$mockProject = new Project(array('id'=>123, 'permissions'=>array()));
		$this->request = new Request(array('post'=>array('id'=>123, 'detailKey'=>'Fish', 'detailValue'=>'Chips')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->create();
		$this->assertInstanceOf('\OCA\AppFramework\Http\JSONResponse', $response);
		$this->assertEquals('403', $response->getStatus());
	}

	public function testCreateDetailWithProjetCreatePerm() {
		$this->request = new Request(array('post'=>array('id'=>123, 'detailKey'=>'Fish', 'detailValue'=>'Chips')));
		$mockProject = new Project(array('id'=>123, 'permissions'=>array('create')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$mockDetail = new Detail(array('id'=>7, 'projectId'=>9, 'detailKey'=>'Fish', 'detailValue'=>'Chips'));
		$mockDetailMapper = $this->getMock('DetailMapper', array('insert', 'getDetails', 'getDetail'));
		$mockDetailMapper->expects($this->once())->method('getDetails')->will($this->returnValue($mockDetail));
		$mockDetailMapper->expects($this->once())->method('getDetail')->will($this->returnValue(null));
		$mockDetailMapper->expects($this->once())->method('insert')->will($this->returnValue($mockDetail));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->create();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('detail/index', $response->getTemplateName());
	}

	public function testCreateWillOverwriteDetailIfKeyAlreadyExists() {
		$this->request = new Request(array('post'=>array('id'=>123, 'detailKey'=>'Fish', 'detailValue'=>'Chips')));
		$mockProject = new Project(array('id'=>123, 'permissions'=>array('create')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$mockDetail = new Detail(array('id'=>7, 'projectId'=>9, 'detailKey'=>'Fish', 'detailValue'=>'Chips'));
		$mockDetailMapper = $this->getMock('DetailMapper', array('update', 'getDetails', 'getDetail'));
		$mockDetailMapper->expects($this->once())->method('getDetails')->will($this->returnValue($mockDetail));
		$mockDetailMapper->expects($this->once())->method('getDetail')->will($this->returnValue($mockDetail));
		$mockDetailMapper->expects($this->once())->method('update')->will($this->returnValue($mockDetail));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->create();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('detail/index', $response->getTemplateName());
	}

	public function testUpdateAnnotations() {
		$this->assertAnnotations($this->controller, 'update', array('IsAdminExemption', 'IsSubAdminExemption'));
	}
	
	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Project Id not set
	 */
	public function testUpdateDetailNoProject() {
		$this->controller->update();
	}

	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Detail Key not set
	 */
	public function testUpdateDetailNoDetail_key() {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue(123));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$this->controller->update();
	}

	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Detail Value not set
	 */
	public function testUpdateDetailNoDetail_value() {
		$this->request = new Request(array('post'=>array('id'=>123, 'detailKey'=>'Fish')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue(123));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$this->controller->update();
	}

	public function testUpdateDetailNoProjectUpdatePerm() {
		$mockProject = new Project(array('id'=>123, 'permissions'=>array()));
		$this->request = new Request(array('post'=>array('id'=>123, 'detailKey'=>'Fish', 'detailValue'=>'Chips')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->update();
		$this->assertInstanceOf('\OCA\AppFramework\Http\JSONResponse', $response);
		$this->assertEquals('403', $response->getStatus());
	}

	public function testUpdateDetailWithProjetUpdatePerm() {
		$this->request = new Request(array('post'=>array('id'=>123, 'detailKey'=>'Fish', 'detailValue'=>'Chips')));
		$mockProject = new Project(array('id'=>123, 'permissions'=>array('update')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$mockDetail = $this->getMock('Detail', array('setDetailValue', 'getDetailKey'));
		$mockDetail->expects($this->once())->method('getDetailKey')->will($this->returnValue('Fish'));
		$mockDetailMapper = $this->getMock('DetailMapper', array('update', 'getDetail'));
		$mockDetailMapper->expects($this->once())->method('getDetail')->will($this->returnValue($mockDetail));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/project/123/detail'));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->update();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/projects/project/123/detail', $response->getRedirectURL());
		$this->assertEquals(303, $response->getStatus());
	}
	
	public function testDestroyAnnotations() {
		$this->assertAnnotations($this->controller, 'destroy', array('IsAdminExemption', 'IsSubAdminExemption'));
	}
	
	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Project Id not set
	 */
	public function testDestroyDetailNoProject() {
		$this->controller->destroy();
	}

	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Detail Key not set
	 */
	public function testDestroyDetailNoDetail_key() {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue(123));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$this->controller->destroy();
	}

	public function testDestroyDetailNoProjectDestroyPerm() {
		$this->request = new Request(array('post'=>array('id'=>123, 'detailKey'=>'Fish')));
		$mockProject = array('id'=>123, 'permissions'=>array());
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$mockDetail = new Detail();
		$mockDetailMapper = $this->getMock('DetailMapper', array('update', 'getDetail'));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('\OCA\AppFramework\Http\JSONResponse', $response);
		$this->assertEquals('403', $response->getStatus());
	}

	public function testDestroyDetailWithProjetDestroyPerm() {
		$this->request = new Request(array('post'=>array('id'=>123, 'detailKey'=>'Fish')));
		$mockProject = array('id'=>123, 'permissions'=>array('delete'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$mockDetailMapper = $this->getMock('DetailMapper', array('delete', 'getDetail'));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/project/123/detail'));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/project/123/detail', $response->getRedirectURL());
		$this->assertEquals(303, $response->getStatus());
	}
	
	public function testDeleteAnnotations() {
		$this->assertAnnotations($this->controller, 'delete', array('IsAdminExemption', 'IsSubAdminExemption', 'CSRFExemption'));
	}

	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Project Id not set
	 */
	public function testDeleteDetailNoProject() {
		$this->controller->delete();
	}

	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Detail Key not set
	 */
	public function testDeleteDetailNoDetail_key() {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue(123));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->delete();
		$this->assertInstanceOf('\OCA\AppFramework\Http\JSONResponse', $response);
		$this->assertEquals('403', $response->getStatus());
	}
	
	public function testDeleteNoDetialForProject() {
		$this->request = new Request(array('post'=>array('id'=>123, 'detailKey'=>'Fish')));
		$mockProject = array('id'=>123, 'permissions'=>array('delete'));
		$mockDetailMapper = $this->getMock('DetailMapper', array('getDetail'));
		$mockDetailMapper->expects($this->once())->method('getDetail')->will($this->returnValue(null));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->delete();
		$this->assertInstanceOf('\OCA\AppFramework\Http\JSONResponse', $response);
		$this->assertEquals('403', $response->getStatus());
	}
	
	public function testDeleteDetail() {
		$this->request = new Request(array('post'=>array('id'=>123, 'detailKey'=>'Fish')));
		$mockProject = array('id'=>123, 'permissions'=>array('delete'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$mockDetailMapper = $this->getMock('DetailMapper', array('getDetail'));
		$mockDetail = new Detail();
		$mockDetailMapper->expects($this->once())->method('getDetail')->will($this->returnValue($mockDetail));
		$this->controller = new DetailController($this->api, $this->request, $mockDetailMapper, $this->projectController);
		$response = $this->controller->delete();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('detail/delete', $response->getTemplateName());
	}

}

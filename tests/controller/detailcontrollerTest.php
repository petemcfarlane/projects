<?php
namespace OCA\Projects\Controller;

use \OCA\AppFramework\Utility\ControllerTestUtility;
use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\RedirectResponse;
use \OCA\Projects\Db\Project;
use \OCA\Projects\Db\Detail;
require_once(__DIR__ . "/../classloader.php");

class DetailControllerTest extends ControllerTestUtility {
	
	private $mockDetail;
	
	public function setUp() {
		$this->api = $this->getAPIMock('OCA\Projects\Core\API');
		$this->api->expects($this->any())->method('getUserId')->will($this->returnValue('Foo'));
		$this->request = new Request(array('get'=>array('id'=>123)));
		$this->projectController = $this->getMock('ProjectController', array('getProject'));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$this->mockDetail = new Detail(array('id'=>10, 'projectId'=>123, 'detailKey'=>'Color', 'detailValue'=>'Blue'));
	}
	
	public function userOrSharedProject() {
		return array(
			array( new Project (array('id'=>123, 'uid'=>'Foo'), 'Foo') ),	 // user project
			array( new Project (array('id'=>123, 'uid'=>'Bar'), 'Foo', 31) ) // shared project with all permissions
		);
	}

	public function noProjectOrPermissions() {
		return array(
			array(	null),													// no project found
			array(new Project (array('id'=>123, 'uid'=>'Bar'), 'Foo', 0))	// shared project but no permissions
		);
	}
	
	public function testIndexAnnotations() {
		$loggedIn = array('IsAdminExemption', 'IsSubAdminExemption');
		$loggedInCSRF = array('IsAdminExemption', 'IsSubAdminExemption', 'CSRFExemption');
		$this->assertAnnotations($this->controller, 'index', $loggedInCSRF);
		$this->assertAnnotations($this->controller, 'create', $loggedIn);
		$this->assertAnnotations($this->controller, 'update', $loggedIn);
		$this->assertAnnotations($this->controller, 'destroy', $loggedIn);
	}

	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testIndexNoProjectOrReadPerms($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}
	
	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testIndexUserProjecectOrSharedWithPerms($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$detailMapper = $this->getMock('detailMapper', array('getDetails'));
		$this->controller = new DetailController($this->api, $this->request, $detailMapper, $this->projectController);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('detail/index', $response->getTemplateName());
	}


	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testCreateNoProjectOrCreatePerm($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->create();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}

	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testCreateUserOrSharedProject($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$detailMapper = $this->getMock('DetailMapper', array('insert','getDetailFromKey', 'getDetails'));
		$detailMapper->expects($this->once())->method('insert')->will($this->returnValue($this->mockDetail));
		$this->request = new Request(array('post'=>array('id'=>123, 'detailKey'=>'Colour', 'detailValue'=>'Blue')));
		$this->controller = new DetailController($this->api, $this->request, $detailMapper, $this->projectController);
		$response = $this->controller->create();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('detail/index', $response->getTemplateName());
	}

	/**
	 * @dataProvider userOrSharedProject
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage detailKey not set
	 */
	public function testCreateNoDetailKeyInRequest($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->request = new Request(array('post'=>array('id'=>123, 'detailValue'=>'Blue')));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$this->controller->create();
	}

	/**
	 * @dataProvider userOrSharedProject
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage detailValue not set
	 */
	public function testCreateNoDetailValueInRequest($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->request = new Request(array('post'=>array('id'=>123, 'detailKey'=>'Color')));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$this->controller->create();
	}

	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testUpdateNoProjectOrNoUpdatePerm($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->update();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}
	
	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testUpdateUserOrSharedProjectNoNote($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->request = new Request(array('post'=>array('id'=>123, 'detailId'=>10, 'detailValue'=>'updated detail')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$detailMapper = $this->getMock('DetailMapper', array('getDetail'));
		$this->controller = new DetailController($this->api, $this->request, $detailMapper, $this->projectController);
		$response = $this->controller->update();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}
	
	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testUpdateUserOrSharedProjectAndNote($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects/123/detail'));
		$this->request = new Request(array('post'=>array('id'=>123, 'detailId'=>10, 'detailValue'=>'updated detail')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$detailMapper = $this->getMock('DetailMapper', array('getDetail', 'update'));
		$updatedDetail = new Detail(array('id'=>10, 'projectId'=>123, 'detailKey'=>'Color', 'detailValue'=>'updated detail'));
		$detailMapper->expects($this->once())->method('getDetail')->will($this->returnValue($this->mockDetail));
		$detailMapper->expects($this->once())->method('update')->will($this->returnValue($updatedDetail));
		$this->controller = new DetailController($this->api, $this->request, $detailMapper, $this->projectController);
		$response = $this->controller->update();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects/123/detail', $response->getRedirectUrl());
	}

	/**
	 * @dataProvider userOrSharedProject
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage detailId not set
	 */
	public function testUpdateNoDetailIdInRequest($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->request = new Request(array('post'=>array('id'=>123, 'detailValue'=>'Blue')));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$this->controller->update();
	}

	/**
	 * @dataProvider userOrSharedProject
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage detailValue not set
	 */
	public function testUpdateNoDetailValueInRequest($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->request = new Request(array('post'=>array('id'=>123, 'detailId'=>10)));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$this->controller->update();
	}


	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testDestroyNoProjectOrDeletePerm($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}
	
	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testDestroyUserOrSharedProjectNoDetail($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects/123/detail'));
		$this->request = new Request(array('post'=>array('id'=>123, 'detailId'=>10)));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$detailMapper = $this->getMock('DetailMapper', array('getDetail'));
		$this->controller = new DetailController($this->api, $this->request, $detailMapper, $this->projectController);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects/123/detail', $response->getRedirectUrl());
	}

	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testDestroyUserOrSharedProjectAndDetail($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects/123/detail'));
		$this->request = new Request(array('post'=>array('id'=>123, 'detailId'=>10)));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$detailMapper = $this->getMock('DetailMapper', array('getDetail', 'delete'));
		$detailMapper->expects($this->once())->method('getDetail')->will($this->returnValue($this->mockDetail));
		$this->controller = new DetailController($this->api, $this->request, $detailMapper, $this->projectController);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects/123/detail', $response->getRedirectUrl());
	}

	/**
	 * @dataProvider userOrSharedProject
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage detailId not set
	 */
	public function testDestroyNoDetailIdInRequest($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->request = new Request(array('post'=>array('id'=>123, 'detailValue'=>'Blue')));
		$this->controller = new DetailController($this->api, $this->request, null, $this->projectController);
		$this->controller->destroy();
	}

}

<?php
namespace OCA\Projects\Controller;

use \OCA\AppFramework\Utility\ControllerTestUtility;
use \OCA\AppFramework\Http\Request;
use \OCA\Projects\Db\Project;
// use \OCA\AppFramework\Http\RedirectResponse;
require_once(__DIR__ . "/../classloader.php");

class TaskControllerTest extends ControllerTestUtility {
	
	public function setUp() {
		$this->api = $this->getAPIMock('OCA\Projects\Core\API');
		$this->api->expects($this->any())->method('getUserId')->will($this->returnValue('Foo'));
		$this->projectController = $this->getMock('ProjectController', array('getProject'));
		$this->request = new Request();
		$this->controller = new TaskController($this->api, $this->request, $this->projectController);
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
	
	public function testAnnotations() {
		$loggedIn = array('IsAdminExemption', 'IsSubAdminExemption');
		$loggedInCSRF = array('IsAdminExemption', 'IsSubAdminExemption', 'CSRFExemption');
		$this->assertAnnotations($this->controller, 'index', $loggedInCSRF);
		$this->assertAnnotations($this->controller, 'create', $loggedIn);
		$this->assertAnnotations($this->controller, 'update', $loggedIn);
		$this->assertAnnotations($this->controller, 'destroy', $loggedIn);
	}

	public function testRedirectProjectsIndex() {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->controller = new TaskController($this->api, $this->request, $this->projectController);
		$response = $this->controller->redirectProjectsIndex();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}

	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage projectId not set
	 */
	public function testGetTasksNoProjectId() {
		$response = $this->controller->getTasks();
	}

	public function testGetTasksNoTasksNoneExist() {
		
	}

	public function testGetTasks() {
		
	}

	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testIndexNoProjectOrReadPerm($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new TaskController($this->api, $this->request, $this->projectController);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}

	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testIndexUserProjectOrReadPerm($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new TaskController($this->api, $this->request, $this->projectController);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('tasks/index', $response->getTemplateName());
	}
	
	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testCreateNoProjectOrCreatePerm($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new TaskController($this->api, $this->request, $this->projectController);
		$response = $this->controller->create();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
		
	}

	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testCreateUserProjectOrCreatePerm($project) {
		
	}

	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testUpdateNoProjectOrUpdatePerm($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new TaskController($this->api, $this->request, $this->projectController);
		$response = $this->controller->update();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
		
	}

	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testUpdateUserProjectOrUpdatePerm($project) {
		
	}

	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testDestroyNoProjectOrDestroyPerm($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new TaskController($this->api, $this->request, $this->projectController);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
		
	}

	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testDestroyUserProjectOrDestroyPerm($project) {
		
	}

}
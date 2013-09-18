<?php
namespace OCA\Projects\Controller;

use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\RedirectResponse;
use \OCA\AppFramework\Utility\ControllerTestUtility;
use \OCA\Projects\Db\Project;
require_once(__DIR__ . "/../classloader.php");

class ProjectControllerTest extends ControllerTestUtility {
	
	private $api;
	private $request;
	private $controller;

	public function setUp() {
		$this->api = $this->getAPIMock('OCA\Projects\Core\API');
		$this->api->expects($this->any())->method('getUserId')->will($this->returnValue('Foo'));
		$this->request = new Request();
		$this->controller = new ProjectController($this->api, $this->request);
	}
	
	public function testProjectsControllerHasRequestToken() {
		$this->assertArrayHasKey('requesttoken', $this->controller->getParams() );
	}
	
	public function testRenderAsDefaultsToUser() {
		$this->assertEquals('user', $this->controller->getRenderAs());
	}

	public function testRenderAsBlankIfPjax() {
		$_SERVER['HTTP_X_PJAX'] = 'true';
		$pjaxRequest = new ProjectController($this->api, $this->request);
		$this->assertEquals('', $pjaxRequest->getRenderAs());
	}
	
	public function testAnnotations() {
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption', 'CSRFExemption');
		$this->assertAnnotations($this->controller, 'index', $annotations);
		$this->assertAnnotations($this->controller, 'show', $annotations);
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption');
		$this->assertAnnotations($this->controller, 'create', $annotations);
		$this->assertAnnotations($this->controller, 'update', $annotations);
		$this->assertAnnotations($this->controller, 'destroy', $annotations);
	}

	public function testIndexReturnsIndexTemplate() {
		$projectMapper = $this->getMock('ProjectMapper', array('getProjects'));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('index', $response->getTemplateName());
	}
	
	public function testCreateReturnsCorrectTemplateResponse() {
		$mockProject = new Project();
		$projectMapper = $this->getMock('ProjectMapper', array('insert', 'getProjects'));
		$projectMapper->expects($this->once())->method('insert')->will($this->returnValue($mockProject));
		$projectMapper->expects($this->once())->method('getProjects')->will($this->returnValue($mockProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->create();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('index', $response->getTemplateName());
	}

	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage uid not set
	 */
	public function testGetProjectsWithNoUser() {
		$this->controller->getProjects();
	}
	
	public function testGetProjectsNoUserNoShared() {
		$projectMapper = $this->getMock('ProjectMapper', array('getProjects'));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->getProjects('Foo');
		$this->assertEmpty($response);
	}
	
	public function testGetProjectsOnlyUserNoShared() {
		$mockUserProjects = array('project1', 'project2', 'project3');
		$projectMapper = $this->getMock('ProjectMapper', array('getProjects'));
		$projectMapper->expects($this->once())->method('getProjects')->will($this->returnValue($mockUserProjects));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->getProjects('Foo');
		$this->assertEquals($mockUserProjects, $response);
	}
	
	public function testGetProjectsOnlySharedNoUser() {
		$mockSharedProjects = array('shared project1', 'shared project2', 'shared project3');
		$projectMapper = $this->getMock('ProjectMapper', array('getProjects'));
		$this->api->expects($this->once())->method('getItemsSharedWith')->will($this->returnValue($mockSharedProjects));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->getProjects('Foo');
		$this->assertEquals($mockSharedProjects, $response);
	}
	
	public function testGetProjectsUserAndShared() {
		$mockSharedProjects = array('shared project1', 'shared project2', 'shared project3');
		$mockUserProjects = array('project1', 'project2', 'project3');
		$projectMapper = $this->getMock('ProjectMapper', array('getProjects'));
		$projectMapper->expects($this->once())->method('getProjects')->will($this->returnValue($mockUserProjects));
		$this->api->expects($this->once())->method('getItemsSharedWith')->will($this->returnValue($mockSharedProjects));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->getProjects('Foo');
		$this->assertEquals(array_merge($mockUserProjects, $mockSharedProjects), $response);
	}

	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage uid not set
	 */
	public function testGetProjectWithNoUserId() {
		$this->controller->getProject(123);
	}
	
	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage projectId not set
	 */
	public function testGetProjectWithNoProjectId() {
		$this->controller->getProject(null, 123);
	}
	
	public function testGetProjectWithUserOwnedProject() {
		$project = new Project(array('id'=>42, 'uid'=>'Foo'), 'Foo');
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->getProject(42, 'Foo');
		$this->assertEquals($project, $response);
	}
	
	public function testGetProjectWithSharedProject() {
		$result = array('id'=>42, 'uid'=>'Bar', 'permissions'=>17);
		$share = array('id'=>123, 'item_source'=>42, 'permissions'=>17);
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($share));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'findProjectById'));
		$projectMapper->expects($this->once())->method('findProjectById')->will($this->returnValue($result));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->getProject(42, 'Foo');
		$this->assertEquals(array('id'=>42, 'uid'=>'Bar', 'permissions'=>17), $response);
	}

	public function testGetProjectWhenProjectDoesNotExist() {
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'findProjectById'));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->getProject(5, 'Foo');
		$this->assertEmpty($response);
	}

	public function testShowProjectDoesNotExist() {
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'findProjectById'));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->show();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testShowProjectByUser() {
		$mockProject = new Project(array('id'=>999, 'uid'=>'Foo'), 'Foo');
		$this->request = new Request(array('get'=>array('id'=>999)));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->show();
		$this->assertInstanceOf('OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('show', $response->getTemplateName());
	}

	public function testShowProjectExistsButNotUserOwnedOrShared() {
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$mockSharedProject = array('id'=>999, 'uid'=>'Bar', 'permissions'=>array());
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'findProjectById'));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->show();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testShowSharedProjectWithReadPerms() {
		$this->request = new Request(array('get'=>array('id'=>999)));
		$share = array('id'=>123, 'item_source'=>42, 'permissions'=>17);
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($share));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'findProjectById'));
		$mockSharedProject = new Project ( array(array('id'=>999, 'uid'=>'Bar'), 'Foo', 17));
		$projectMapper->expects($this->once())->method('findProjectById')->will($this->returnValue($mockSharedProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->show();
		$this->assertInstanceOf('OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('show', $response->getTemplateName());
	}

	public function testUpdateProjectDoesNotExist() {
		$this->request = new Request(array('post'=>array('id'=>999)));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'findProjectById'));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->update();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testUpdateProjectByUser() {
		$mockProject = new Project( array('id'=>999, 'uid'=>'Foo', 'projectName'=>'ACME'), 'Foo' );
		$this->request = new Request(array('post'=>array('id'=>999, 'projectName'=>'technologique')));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'update'));
		$projectMapper->expects($this->once())->method('update')->will($this->returnValue($mockProject));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->update();
		$this->assertInstanceOf('OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('show', $response->getTemplateName());
	}

	public function testUpdateProjectIsSharedButUserDoesNotHaveUpdatePerm() {
		$mockSharedProject = array('id'=>999, 'uid'=>'Bar', 'projectName'=>'ACME');
		$this->request = new Request(array('post'=>array('id'=>999, 'projectName'=>'technologique')));
		$share = array('id'=>123, 'item_source'=>42, 'permissions'=>17);
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($share));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'findProjectById'));
		$mockSharedProject = new Project (array('id'=>999, 'uid'=>'Bar'), 'Foo', 17);
		$projectMapper->expects($this->once())->method('findProjectById')->will($this->returnValue($mockSharedProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->update();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testUpdateProjectIsSharedAndUserHasUpdatePerm() {
		$mockSharedProject = new Project( array('id'=>999, 'uid'=>'Bar', 'projectName'=>'ACME') );
		$this->request = new Request(array('post'=>array('id'=>999, 'projectName'=>'technologique', 'permissions'=>array('update'))));
		$share = array('id'=>123, 'item_source'=>42, 'permissions'=>17);
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($share));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'update', 'findProjectById'));
		$projectMapper->expects($this->once())->method('update')->will($this->returnValue($mockSharedProject));
		$mockSharedProject = new Project (array('id'=>999, 'uid'=>'Bar'), 'Foo', 31);
		$projectMapper->expects($this->once())->method('findProjectById')->will($this->returnValue($mockSharedProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->update();
		$this->assertInstanceOf('OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('show', $response->getTemplateName());
	}

	public function testDestroyProjectDoesNotExist() {
		$this->request = new Request(array('post'=>array('id'=>999)));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'findProjectById'));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testDestroyProjectByUser() {
		$mockProject = new Project( array('id'=>999, 'uid'=>'Foo', 'projectName'=>'ACME'), 'Foo' );
		$this->request = new Request(array('post'=>array('id'=>999)));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'delete'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testDestroyProjectSharedButNoDeletePerm() {
		$mockSharedProject = new Project( array('id'=>999, 'uid'=>'Bar', 'projectName'=>'ACME'), 'Foo' );
		$this->request = new Request(array('post'=>array('id'=>999)));
		$share = array('id'=>123, 'item_source'=>42, 'permissions'=>17);
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($share));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'delete', 'findProjectById'));
		$mockSharedProject = new Project (array('id'=>999, 'uid'=>'Bar'), 'Foo', 1);
		$projectMapper->expects($this->once())->method('findProjectById')->will($this->returnValue($mockSharedProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testDestroyProjectSharedAndDeletePerm() {
		$mockSharedProject = new Project( array('id'=>999, 'uid'=>'Bar', 'projectName'=>'ACME'), 'Foo' );
		$this->request = new Request(array('post'=>array('id'=>999, 'permissions'=>array('delete'))));
		$share = array('id'=>123, 'item_source'=>42, 'permissions'=>17);
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($share));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'delete', 'findProjectById'));
		$mockSharedProject = new Project( array('id'=>999, 'uid'=>'Bar'), 'Foo', 31);
		$projectMapper->expects($this->once())->method('findProjectById')->will($this->returnValue($mockSharedProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

}

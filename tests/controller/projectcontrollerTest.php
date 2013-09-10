<?php
namespace OCA\Projects\Controller;

use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\TemplateResponse;
use \OCA\AppFramework\Http\RedirectResponse;
use \OCA\AppFramework\Utility\ControllerTestUtility;
use \OCA\Projects\Db\Project;
use \OCA\Projects\Db\ProjectMapper;
require_once(__DIR__ . "/../classloader.php");

class ProjectControllerTest extends ControllerTestUtility {
	
	private $api;
	private $request;
	private $controller;

	public function setUp() {
		$this->api = $this->getAPIMock('OCA\Projects\Core\API');
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
	
	public function testAnnotations(){
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption', 'CSRFExemption');
		$this->assertAnnotations($this->controller, 'index', $annotations);
		$this->assertAnnotations($this->controller, 'newForm', $annotations);
		$this->assertAnnotations($this->controller, 'show', $annotations);
		$this->assertAnnotations($this->controller, 'edit', $annotations);
		$this->assertAnnotations($this->controller, 'delete', $annotations);
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption');
		$this->assertAnnotations($this->controller, 'create', $annotations);
		$this->assertAnnotations($this->controller, 'update', $annotations);
		$this->assertAnnotations($this->controller, 'destroy', $annotations);
	}

	public function testIndexReturnsIndexTemplate() {
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo Bar'));
		$mockProjects = array('Project 1', 'Project 2');
		$mockSharedProjects = array('Shared Project 1', 'Shared Project 2', 'Shared Project 3');
		$projectMapper = $this->getMock('ProjectMapper', array('getProjects'));
		$projectMapper->expects($this->once())->method('getProjects')->will($this->returnValue($mockProjects));
		$this->api->expects($this->once())->method('getItemsSharedWith')->will($this->returnValue($mockSharedProjects));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('index', $response->getTemplateName());
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetProjectsWithNoUser() {
		$this->assertInternalType('array', $this->controller->getProjects());
	}
	
	public function testGetProjectsNoUserNoShared() {
		$projectMapper = $this->getMock('ProjectMapper', array('getProjects'));
		$projectMapper->expects($this->once())->method('getProjects')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->getProjects('Foo');
		$this->assertEmpty($response);
	}
	
	public function testGetProjectsOnlyUserNoShared() {
		$mockUserProjects = array('project1', 'project2', 'project3');
		$projectMapper = $this->getMock('ProjectMapper', array('getProjects'));
		$projectMapper->expects($this->once())->method('getProjects')->will($this->returnValue($mockUserProjects));
		$this->api->expects($this->once())->method('getItemsSharedWith')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->getProjects('Foo');
		$this->assertEquals($mockUserProjects, $response);
	}
	
	public function testGetProjectsOnlySharedNoUser() {
		$mockSharedProjects = array('shared project1', 'shared project2', 'shared project3');
		$projectMapper = $this->getMock('ProjectMapper', array('getProjects'));
		$projectMapper->expects($this->once())->method('getProjects')->will($this->returnValue(null));
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
	
	public function testNewProjectForm() {
		$response = $this->controller->newForm();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('new', $response->getTemplateName());
	}

	public function testCreateReturnsCorrectTemplateResponse() {
		$mockProjects = array('Project 1', 'Project 2');
		$projectMapper = $this->getMock('ProjectMapper', array('insert', 'getProjects'));
		$projectMapper->expects($this->once())->method('insert')->will($this->returnValue($mockProjects));
		$projectMapper->expects($this->once())->method('getProjects')->will($this->returnValue($mockProjects));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo Bar'));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->create();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('index', $response->getTemplateName());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetProjectWithNoUserId() {
		$this->assertInternalType('array', $this->controller->getProject());
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetProjectWithNoProjectId() {
		$this->assertInternalType('array', $this->controller->getProject());
	}
	
	public function testGetProjectWithUserOwnedProject() {
		$project = array('id'=>42, 'uid'=>'Foo');
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->getProject(42, 'Foo');
		$this->assertEquals($project, $response);
	}
	
	public function testGetProjectWithSharedProject() {
		$project = array('id'=>42, 'uid'=>'Bar', 'permissions'=>array('read','update'));
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($project));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->getProject(42, 'Foo');
		$this->assertEquals($project, $response);
	}

	public function testGetProjectWhenProjectDoesNotExist() {
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue(null));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->getProject(5, 'Foo');
		$this->assertEmpty($response);
	}

	public function testShowProjectDoesNotExist() {
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo Bar'));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->show();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testShowProjectByUser() {
		$mockProject = array('id'=>999, 'uid'=>'Foo');
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->show();
		$this->assertInstanceOf('OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('show', $response->getTemplateName());
	}

	public function testShowProjectExistsButNotUserOwnedOrShared() {
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$mockSharedProject = array('id'=>999, 'uid'=>'Bar', 'permissions'=>array());
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($mockSharedProject));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->show();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testShowSharedProjectWithReadPerms() {
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$mockSharedProject = array('id'=>999, 'uid'=>'Bar', 'permissions'=>array('read'));
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($mockSharedProject));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->show();
		$this->assertInstanceOf('OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('show', $response->getTemplateName());
	}

	public function testEditProjectDoesNotExist() {
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo Bar'));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->edit();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}
	
	public function testEditProjectByUser() {
		$mockProject = array('id'=>999, 'uid'=>'Foo');
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->edit();
		$this->assertInstanceOf('OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('edit', $response->getTemplateName());
	}
	
	public function testEditProjectIsSharedButUserDoesNotHaveUpdatePerm() {
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$mockSharedProject = array('id'=>999, 'uid'=>'Bar', 'permissions'=>array('read'));
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($mockSharedProject));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->edit();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}
	
	public function testEditProjectIsSharedAndUserHasUpdatePerm() {
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$mockSharedProject = array('id'=>999, 'uid'=>'Bar', 'permissions'=>array('update'));
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($mockSharedProject));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->edit();
		$this->assertInstanceOf('OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('edit', $response->getTemplateName());
	}

	public function testUpdateProjectDoesNotExist() {
		$this->request = new Request(array('post'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo Bar'));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->update();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}
	
	public function testUpdateProjectByUser() {
		$mockProject = new Project( array('id'=>999, 'uid'=>'Foo', 'projectName'=>'ACME') );
		$this->request = new Request(array('post'=>array('id'=>999, 'projectName'=>'technologique')));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/project/999'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'update'));
		$projectMapper->expects($this->once())->method('update')->will($this->returnValue($mockProject));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->update();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/project/999', $response->getRedirectURL());
	}
	
	public function testUpdateProjectIsSharedButUserDoesNotHaveUpdatePerm() {
		$mockSharedProject = new Project( array('id'=>999, 'uid'=>'Bar', 'projectName'=>'ACME') );
		$this->request = new Request(array('post'=>array('id'=>999, 'projectName'=>'technologique')));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($mockSharedProject));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'update'));
		$projectMapper->expects($this->once())->method('update')->will($this->returnValue($mockSharedProject));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->update();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}
	
	public function testUpdateProjectIsSharedAndUserHasUpdatePerm() {
		$mockSharedProject = new Project( array('id'=>999, 'uid'=>'Bar', 'projectName'=>'ACME') );
		$this->request = new Request(array('post'=>array('id'=>999, 'projectName'=>'technologique', 'permissions'=>array('update'))));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($mockSharedProject));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/project/999'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'update'));
		$projectMapper->expects($this->once())->method('update')->will($this->returnValue($mockSharedProject));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->update();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/project/999', $response->getRedirectURL());
	}

	public function testDeleteProjectDoesNotExist() {
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo Bar'));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->delete();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testDeleteProjectByUser() {
		$mockProject = array('id'=>999, 'uid'=>'Foo');
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->delete();
		$this->assertInstanceOf('OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('delete', $response->getTemplateName());
	}
	
	public function testDeleteProjectIsSharedButUserDoesNotHaveDeletePerm() {
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$mockSharedProject = array('id'=>999, 'uid'=>'Bar', 'permissions'=>array('read', 'update'));
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($mockSharedProject));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->delete();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testDeleteProjectIsSharedAndUserHasDeletePerm() {
		$this->request = new Request(array('get'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$mockSharedProject = array('id'=>999, 'uid'=>'Bar', 'permissions'=>array('update','delete'));
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($mockSharedProject));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->delete();
		$this->assertInstanceOf('OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('delete', $response->getTemplateName());
	}

	public function testDestroyProjectDoesNotExist() {
		$this->request = new Request(array('post'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo Bar'));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testDestroyProjectByUser() {
		$mockProject = new Project( array('id'=>999, 'uid'=>'Foo', 'projectName'=>'ACME') );
		$this->request = new Request(array('post'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'delete'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testDestroyProjectSharedButNoDeletePerm() {
		$mockSharedProject = new Project( array('id'=>999, 'uid'=>'Bar', 'projectName'=>'ACME') );
		$this->request = new Request(array('post'=>array('id'=>999)));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($mockSharedProject));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'delete'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}

	public function testDestroyProjectSharedAndDeletePerm() {
		$mockSharedProject = new Project( array('id'=>999, 'uid'=>'Bar', 'projectName'=>'ACME') );
		$this->request = new Request(array('post'=>array('id'=>999, 'permissions'=>array('delete'))));
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo'));
		$this->api->expects($this->once())->method('getItemSharedWith')->will($this->returnValue($mockSharedProject));
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/'));
		$projectMapper = $this->getMock('ProjectMapper', array('getProject', 'delete'));
		$projectMapper->expects($this->once())->method('getProject')->will($this->returnValue(null));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/', $response->getRedirectURL());
	}
}

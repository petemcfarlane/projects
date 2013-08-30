<?php
namespace OCA\Projects\Controller;

/*
use \Exception;
use \OCA\AppFramework\Http\JSONResponse;
*/
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
	
	public function testIndexReturnsIndexTemplate() {
		$this->api->expects($this->once())->method('getUserId')->will($this->returnValue('Foo Bar'));
		
		$mockSharedProjects = array('Shared Project 1', 'Shared Project 2', 'Shared Project 3');
		$this->api->expects($this->once())->method('getItemsSharedWith')->will($this->returnValue($mockSharedProjects));
		
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
	
	public function testNewProjectForm() {
		$response = $this->controller->newForm();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('new', $response->getTemplateName());
	}

	public function testCreateReturnsCorrectRedirectResponse() {
		$mockProject = new Project(array('id'=>123));
		$projectMapper = $this->getMock('ProjectMapper', array('insert'));
		$projectMapper->expects($this->once())->method('insert')->will($this->returnValue($mockProject));
		
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/projects/project/123'));
		$this->controller = new ProjectController($this->api, $this->request, $projectMapper);
		
		$response = $this->controller->create();

		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response);
		$this->assertEquals('index.php/projects/project/123', $response->getRedirectURL());
	}

	public function testShowProjectDoesNotExist() {
		
	}

	public function testShowProjectExistsButNotOwnedByUserOrNoReadPermissions() {
		
	}

	public function testShowsProjectIfOwnedByUser() {
		$this->assertInstanceOf('OCA\AppFramework\Http\TemplateResponse', $response);
		$this->assertEquals('show', $response->getTemplateName());
		
	}

	public function testAnnotations(){
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption', 'CSRFExemption');
		$this->assertAnnotations($this->controller, 'index', $annotations);
		$this->assertAnnotations($this->controller, 'newForm', $annotations);
		$this->assertAnnotations($this->controller, 'show', $annotations);
		// $this->assertAnnotations($this->controller, 'edit', $annotations);
		// $this->assertAnnotations($this->controller, 'delete', $annotations);
		
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption');
		$this->assertAnnotations($this->controller, 'create', $annotations);
		// $this->assertAnnotations($this->controller, 'update', $annotations);
		// $this->assertAnnotations($this->controller, 'destroy', $annotations);
	}

}

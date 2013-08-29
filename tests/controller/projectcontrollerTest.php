<?php
namespace OCA\Projects\Controller;

/*
use \Exception;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http\RedirectResponse;
use \OCA\SalesQuestionnaire\Db\Questionnaire;
use \OCA\SalesQuestionnaire\Db\QuestionnaireMapper;
*/
use \OCA\AppFramework\Http\TemplateResponse;
use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Utility\ControllerTestUtility;
require_once(__DIR__ . "/../classloader.php");

class ProjectControllerTest extends ControllerTestUtility {
	
	private $api;
	private $request;
	private $controller;

	public function setUp() {
		$this->api = $this->getAPIMock();
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
		$this->api->expects($this->once())
				  ->method('getUserId')
				  ->will($this->returnValue('Foo Bar'));
		
		\OCP\Share::registerBackend('projects', '\OCA\Projects\Lib\Share\ShareProject');
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

	public function testAnnotations(){
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption', 'CSRFExemption');
		$this->assertAnnotations($this->controller, 'index', $annotations);
		$this->assertAnnotations($this->controller, 'newForm', $annotations);
		// $this->assertAnnotations($this->controller, 'show', $annotations);
		// $this->assertAnnotations($this->controller, 'edit', $annotations);
		// $this->assertAnnotations($this->controller, 'delete', $annotations);
		
		$annotations = array('IsAdminExemption', 'IsSubAdminExemption');
		$this->assertAnnotations($this->controller, 'create', $annotations);
		// $this->assertAnnotations($this->controller, 'update', $annotations);
		// $this->assertAnnotations($this->controller, 'destroy', $annotations);
	}

	public function testCreate() {
		//
	}
}
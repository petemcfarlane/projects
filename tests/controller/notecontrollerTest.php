<?php
namespace OCA\Projects\Controller;

use \OCA\AppFramework\Utility\ControllerTestUtility;
use \OCA\AppFramework\Http\Request;
use \OCA\Projects\Db\Project;
use \OCA\AppFramework\Http\RedirectResponse;
use \OCA\Projects\Db\Note;
// use \OCA\AppFramework\Http\TemplateResponse;
// use \OCA\AppFramework\Http\JSONResponse;
// use \OCA\Projects\Db\DetailMapper;
// use \OCA\Projects\Db\ProjectMapper;
// use \OCA\Projects\Controller\ProjectController;
require_once(__DIR__ . "/../classloader.php");

class NotesControllerTest extends ControllerTestUtility {
	
	// private $api;
	// private $request;
	// private $controller;
	// private $projectController;

	public function setUp() {
		$this->api = $this->getAPIMock('OCA\Projects\Core\API');
		$this->api->expects($this->any())->method('getUserId')->will($this->returnValue('Foo'));
		$this->request = new Request(array('get'=>array('id'=>123)));
		$this->projectController = $this->getMock('ProjectController', array('getProject'));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
	}

	public function testIndexAnnotations(){
		$loggedIn = array('IsAdminExemption', 'IsSubAdminExemption');
		$loggedInCSRF = array('IsAdminExemption', 'IsSubAdminExemption', 'CSRFExemption');
		$this->assertAnnotations($this->controller, 'index', $loggedInCSRF);
		$this->assertAnnotations($this->controller, 'show', $loggedInCSRF);
		$this->assertAnnotations($this->controller, 'create', $loggedIn);
	}

	public function testIndexNoProject() {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}

	public function testIndexUserProject() {
		$mockProject = new Project (array('id'=>123, 'uid'=>'Foo'), 'Foo');
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$noteMapper = $this->getMock('NoteMapper', array('getNotes'));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('notes/index', $response->getTemplateName());
	}

	public function testIndexSharedNoReadPerm() {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$mockSharedProject = new Project (array('id'=>123, 'uid'=>'Bar'), 'Foo', 0);
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockSharedProject));
		$noteMapper = $this->getMock('NoteMapper', array('getNotes'));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}

	public function testIndexSharedWithReadPerm() {
		$mockSharedProject = new Project (array('id'=>123, 'uid'=>'Bar'), 'Foo', 1);
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockSharedProject));
		$noteMapper = $this->getMock('NoteMapper', array('getNotes'));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('notes/index', $response->getTemplateName());
	}

	public function testShowNoProjectOrNoReadPerm() {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->show();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}

	public function testShowUserProjectNoNote() {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects/project/123/notes'));
		$mockProject = new Project (array('id'=>123, 'uid'=>'Foo'), 'Foo');
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$noteMapper = $this->getMock('NoteMapper', array('getNote'));
		$noteMapper->expects($this->once())->method('getNote')->will($this->returnValue(null));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->show();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects/project/123/notes', $response->getRedirectUrl());
	}

	public function testShowUserProjectAndNote() {
		$mockProject = new Project (array('id'=>123, 'uid'=>'Foo'), 'Foo');
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$noteMapper = $this->getMock('NoteMapper', array('getNote'));
		$mockNote = new Note();
		$noteMapper->expects($this->once())->method('getNote')->will($this->returnValue($mockNote));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->show();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('notes/show', $response->getTemplateName());
	}

	public function testShowSharedProjectNoNote() {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects/project/123/notes'));
		$mockSharedProject = new Project (array('id'=>123, 'uid'=>'Bar'), 'Foo', 1);
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockSharedProject));
		$noteMapper = $this->getMock('NoteMapper', array('getNote'));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->show();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects/project/123/notes', $response->getRedirectUrl());
	}

	public function testShowSharedProjectAndNote() {
		$mockSharedProject = new Project (array('id'=>123, 'uid'=>'Bar'), 'Foo', 1);
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockSharedProject));
		$noteMapper = $this->getMock('NoteMapper', array('getNote'));
		$mockNote = new Note();
		$noteMapper->expects($this->once())->method('getNote')->will($this->returnValue($mockNote));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->show();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('notes/show', $response->getTemplateName());
	}

	public function testCreateNoProjectOrCreatePerm() {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->create();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}

	public function testCreateUserProject() {
		$mockProject = new Project (array('id'=>123, 'uid'=>'Foo'), 'Foo');
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$noteMapper = $this->getMock('NoteMapper', array('insert'));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->create();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('notes/index', $response->getTemplateName());
	}

	public function testCreateSharedNoCreatePerm() {
		
	}

	public function testCreateSharedAndCreatePerm() {
		
	}

}

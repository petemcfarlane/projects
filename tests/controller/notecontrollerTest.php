<?php
namespace OCA\Projects\Controller;

use \OCA\AppFramework\Utility\ControllerTestUtility;
use \OCA\AppFramework\Http\Request;
use \OCA\Projects\Db\Project;
use \OCA\AppFramework\Http\RedirectResponse;
use \OCA\Projects\Db\Note;
require_once(__DIR__ . "/../classloader.php");

class NotesControllerTest extends ControllerTestUtility {
	
	private $mockNote;

	public function setUp() {
		$this->api = $this->getAPIMock('OCA\Projects\Core\API');
		$this->api->expects($this->any())->method('getUserId')->will($this->returnValue('Foo'));
		$this->request = new Request(array('get'=>array('id'=>123)));
		$this->projectController = $this->getMock('ProjectController', array('getProject'));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$this->mockNote = new Note(array('id'=>10, 'projectId'=>123, 'note'=>'User note for the note'));
	}

	public function userOrSharedProject() {
		return array(
			array( new Project (array('id'=>123, 'uid'=>'Foo'), 'Foo') ),	 // user project
			array( new Project (array('id'=>123, 'uid'=>'Bar'), 'Foo', 31) ) // shared project with all permissions
		);
	}

	public function noProjectOrPermissions() {
		return array(
			array(null),													// no project found
			array(new Project (array('id'=>123, 'uid'=>'Bar'), 'Foo', 0))	// shared project but no permissions
		);
	}
	
	public function testIndexAnnotations() {
		$loggedIn = array('IsAdminExemption', 'IsSubAdminExemption');
		$loggedInCSRF = array('IsAdminExemption', 'IsSubAdminExemption', 'CSRFExemption');
		$this->assertAnnotations($this->controller, 'index', $loggedInCSRF);
		$this->assertAnnotations($this->controller, 'show', $loggedInCSRF);
		$this->assertAnnotations($this->controller, 'newNote', $loggedInCSRF);
		$this->assertAnnotations($this->controller, 'create', $loggedIn);
		$this->assertAnnotations($this->controller, 'update', $loggedIn);
		$this->assertAnnotations($this->controller, 'destroy', $loggedIn);
	}

	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testIndexNoProjectOrNoReadPerm($project) {
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
	public function testIndexUserProjectOrSharedProject($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$noteMapper = $this->getMock('NoteMapper', array('getNotes'));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->index();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('notes/index', $response->getTemplateName());
	}

	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testShowNoProjectOrNoReadPerm($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->show();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}

	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testShowUserOrSharedProjectNoNote($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects/project/123/notes'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$noteMapper = $this->getMock('NoteMapper', array('getNote'));
		$this->request = new Request(array('get'=>array('id'=>123, 'noteId'=>10)));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->show();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects/project/123/notes', $response->getRedirectUrl());
	}

	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testShowUserOrSharedProjectWithNote($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$noteMapper = $this->getMock('NoteMapper', array('getNote'));
		$noteMapper->expects($this->once())->method('getNote')->will($this->returnValue($this->mockNote));
		$this->request = new Request(array('get'=>array('id'=>123, 'noteId'=>10)));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->show();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('notes/show', $response->getTemplateName());
	}

	/**
	 * @dataProvider userOrSharedProject
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage noteId not set
	 */
	public function testShowNoNoteIdInRequest($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		// $this->request = new Request(array('post'=>array('id'=>123, 'detailValue'=>'Blue')));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$this->controller->show();
	}

	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testNewNoteNoProjectOrCreatePerm($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$noteMapper = $this->getMock('NoteMapper', array('getNotes'));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->newNote();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}

	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testNewNoteUserOrSharedProject($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->newNote();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('notes/new', $response->getTemplateName());
	}

	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testCreateNoProjectOrCreatePerm($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->create();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}

	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testCreateUserOrSharedProject($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects/123/notes/62'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$noteMapper = $this->getMock('NoteMapper', array('insert'));
		$noteMapper->expects($this->once())->method('insert')->will($this->returnValue($this->mockNote));
		$this->request = new Request(array('post'=>array('id'=>123, 'note'=>10)));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->create();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects/123/notes/62', $response->getRedirectUrl());
	}

	/**
	 * @dataProvider userOrSharedProject
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage note not set
	 */
	public function testCreateNoNoteInRequest($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$this->controller->create();
	}

	/**
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Request data not set
	 */
	public function testNoteFromRequest() {
		$this->controller->noteFromRequest();
	}
	
	public function testNoteFromRequestReturnsNote() {
		$mockProject = new Project (array('id'=>123, 'uid'=>'Foo'), 'Foo');
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($mockProject));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->noteFromRequest(array('note'=>'blah blah blah'));
		$this->assertInstanceOf('\OCA\Projects\Db\Note', $response);
		$this->assertEquals($response->getNote(), 'blah blah blah');
	}
	
	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testUpdateNoProjectOrNoUpdatePerm($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->update();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}
	
	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testUpdateUserOrSharedProjectNoNote($project) {
		$this->request = new Request(array('get'=>array('id'=>123), 'post'=>array('noteId'=>10, 'note'=>'updated text for the note')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$noteMapper = $this->getMock('NoteMapper', array('getNote', 'insert'));
		$noteMapper->expects($this->once())->method('insert')->will($this->returnValue($this->mockNote));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->update();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('notes/show', $response->getTemplateName());
	}
	
	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testUpdateUserOrSharedProjectAndNote($project) {
		$this->request = new Request(array('get'=>array('id'=>123), 'post'=>array('noteId'=>10, 'note'=>'updated text for the note')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$noteMapper = $this->getMock('NoteMapper', array('getNote', 'update'));
		$updatedNote = new Note(array('id'=>10, 'projectId'=>123, 'note'=>'updated text for the note'));
		$noteMapper->expects($this->once())->method('getNote')->will($this->returnValue($this->mockNote));
		$noteMapper->expects($this->once())->method('update')->will($this->returnValue($updatedNote));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->update();
		$this->assertInstanceOf('\OCA\AppFramework\Http\TemplateResponse', $response );
		$this->assertEquals('notes/show', $response->getTemplateName());
	}
		
	/**
	 * @dataProvider userOrSharedProject
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage noteId not set
	 */
	public function testUpdateNoNoteIdInRequest($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->request = new Request(array('post'=>array('id'=>123, 'note'=>'note about something')));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$this->controller->update();
	}

	/**
	 * @dataProvider userOrSharedProject
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage note not set
	 */
	public function testUpdateNoNoteInRequest($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->request = new Request(array('post'=>array('id'=>123, 'noteId'=>10)));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$this->controller->update();
	}

	/**
	 * @dataProvider noProjectOrPermissions
	 */
	public function testDestroyNoProjectOrDeletePerm($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects'));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects', $response->getRedirectUrl());
	}
	
	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testDestroyUserOrSharedProjectNoNote($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects/123/notes'));
		$this->request = new Request(array('get'=>array('id'=>123), 'post'=>array('noteId'=>10, 'note'=>'updated text for the note')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$noteMapper = $this->getMock('NoteMapper', array('getNote', 'delete'));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects/123/notes', $response->getRedirectUrl());
	}

	/**
	 * @dataProvider userOrSharedProject
	 */
	public function testDestroyUserOrSharedProjectAndNote($project) {
		$this->api->expects($this->once())->method('linkToRoute')->will($this->returnValue('index.php/apps/projects/123/notes'));
		$this->request = new Request(array('get'=>array('id'=>123), 'post'=>array('noteId'=>10, 'note'=>'updated text for the note')));
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$noteMapper = $this->getMock('NoteMapper', array('getNote', 'delete'));
		$noteMapper->expects($this->once())->method('getNote')->will($this->returnValue($this->mockNote));
		$this->controller = new NotesController($this->api, $this->request, $noteMapper, $this->projectController);
		$response = $this->controller->destroy();
		$this->assertInstanceOf('\OCA\AppFramework\Http\RedirectResponse', $response );
		$this->assertEquals('index.php/apps/projects/123/notes', $response->getRedirectUrl());
	}

	/**
	 * @dataProvider userOrSharedProject
	 * @expectedException InvalidArgumentException
     * @expectedExceptionMessage noteId not set
	 */
	public function testDestroyNoNoteIdInRequest($project) {
		$this->projectController->expects($this->once())->method('getProject')->will($this->returnValue($project));
		$this->request = new Request(array('post'=>array('id'=>123)));
		$this->controller = new NotesController($this->api, $this->request, null, $this->projectController);
		$this->controller->destroy();
	}


}

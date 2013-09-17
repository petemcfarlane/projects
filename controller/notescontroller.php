<?php

namespace OCA\Projects\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\Projects\Core\API;
use \OCA\AppFramework\Http\Request;
use \OCA\Projects\Db\NoteMapper;
use \OCA\Projects\Controller\ProjectController;
// use \OCA\AppFramework\Http\TemplateResponse;
use \OCA\AppFramework\Http\RedirectResponse;
// use \OCA\AppFramework\Http\JSONResponse;
use \OCA\Projects\Db\Note;

class NotesController extends Controller {
	
	private $params;
	private $renderas;
	private $noteMapper;
	private $project;
	
	public function __construct(API $api, Request $request, $noteMapper=null, $projectController=null) {
		parent::__construct($api, $request);
		$this->params = array('requesttoken' => \OC_Util::callRegister() );
		$this->renderas = isset($_SERVER['HTTP_X_PJAX']) ? '' : 'user';
		$this->noteMapper = $noteMapper===null ? new NoteMapper($this->api) : $noteMapper;
		$projectController = $projectController===null ? new ProjectController($this->api, $this->request) : $projectController;
		$this->project = $projectController->getProject($this->request->id, $this->api->getUserId());
	}
	
	public function noteFromRequest($request=null) {
		if ($request===null) Throw new \InvalidArgumentException("Request data not set");
		$note = new Note;
		$note->setNote($request['note']);
		$note->setProjectId($this->project->getId());
		if (isset($request['noteId'])) $note->setId($request['noteId']);
		return $note;
	}

	public function redirectProjectsIndex() {
		$response = new RedirectResponse( $this->api->linkToRoute('projects.project.index') );
		$response->setStatus(303);
		return $response;
	}
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function index() {
		if (!$this->project || !$this->project->canRead() ) return $this->redirectProjectsIndex();
		$this->params = array_merge($this->params, (array)$this->project);
		$this->params['notes'] = $this->noteMapper->getNotes( $this->project->getId() );
		return $this->render('notes/index', $this->params, $this->renderas,  array('X-PJAX-URL'=>$this->api->linkToRoute('projects.notes.index', array('id'=>$this->project->getId()))));
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function show() {
		if (!$this->project || !$this->project->canRead() ) return $this->redirectProjectsIndex();
		if ( $note = $this->noteMapper->getNote($this->request->noteId) ) {
			$this->params = array_merge($this->params, (array)$this->project);
			$this->params['note'] = (array)$note;
			return $this->render('notes/show', $this->params, $this->renderas);
		}
		$response = new RedirectResponse( $this->api->linkToRoute('projects.notes.index', array('id'=>$this->project->id)) );
		$response->setStatus(303);
		return $response;
	}
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function newNote() {
		if (!$this->project || !$this->project->canCreate() ) return $this->redirectProjectsIndex();
		$this->params = array_merge($this->params, (array)$this->project);
		return $this->render('notes/new', $this->params, $this->renderas);
	}
	
	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function create() {
		if (!$this->project || !$this->project->canCreate() ) return $this->redirectProjectsIndex();
		$this->params = array_merge($this->params, (array)$this->project);
		$note = $this->noteFromRequest($this->request);
		$note = $this->noteMapper->insert($note);
		$response = new RedirectResponse( $this->api->linkToRoute('projects.notes.show', array('id'=>$note->getProjectId(), 'noteId'=>$note->getId() ) ) );
		$response->setStatus(303);
		return $response;
	}
	
	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function update() {
		if (!$this->project || !$this->project->canUpdate() ) return $this->redirectProjectsIndex();
		$this->params = array_merge($this->params, (array)$this->project);
		if ( $note = $this->noteMapper->getNote($this->request->noteId) ) {
			$note->setNote($this->request->note);
			$this->noteMapper->update($note);
		} else {
			$note = $this->noteFromRequest($this->request);
			$this->noteMapper->insert($note);
		}
		$this->params['note'] = (array)$note;
		return $this->render('notes/show', $this->params, $this->renderas);
	}
	
	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function destroy() {
		if (!$this->project || !$this->project->canDelete() ) return $this->redirectProjectsIndex();
		$note = $this->noteMapper->getNote($this->request->noteId);
		if ($note) $this->noteMapper->delete($note);
		$response = new RedirectResponse( $this->api->linkToRoute('projects.notes.index', array('id'=>$this->project->getId())) );
		$response->setStatus(303);
		return $response;
	}
}
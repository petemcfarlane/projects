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
// use \OCA\Projects\Db\Detail;

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
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function index() {
		if (!$this->project || !$this->project->canRead() ) {
			$response = new RedirectResponse( $this->api->linkToRoute('projects.project.index') );
			$response->setStatus(303);
			return $response;
		}
		$this->params = array_merge($this->params, (array)$this->project);
		$this->params['notes'] = $this->noteMapper->getNotes( $this->project->getId() );
		return $this->render('notes/index', $this->params, $this->renderas);
	}

	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function show() {
		if (!$this->project || !$this->project->canRead() ) {
			$response = new RedirectResponse( $this->api->linkToRoute('projects.project.index') );
			$response->setStatus(303);
			return $response;
		}
		$this->params = array_merge($this->params, (array)$this->project);
		$note = $this->noteMapper->getNote($this->request->noteId);
		if ($note) {
			$this->params['note'] = $note;
			return $this->render('notes/show', $this->params, $this->renderas);
		} else {
			$response = new RedirectResponse( $this->api->linkToRoute('projects.notes.index', array('id'=>$this->project->id)) );
		}
		$response->setStatus(303);
		return $response;
	}
	
	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function create() {
		if (!$this->project || !$this->project->canCreate() ) {
			$response = new RedirectResponse( $this->api->linkToRoute('projects.project.index') );
			$response->setStatus(303);
			return $response;
		}
	}
}
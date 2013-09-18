<?php

namespace OCA\Projects\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\Projects\Core\API;
use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\RedirectResponse;

class TaskController extends Controller {
	
	public function __construct(API $api, Request $request, $projectController=null) {
		parent::__construct($api, $request);
		$this->params = array('requesttoken' => \OC_Util::callRegister() );
		$this->renderas = isset($_SERVER['HTTP_X_PJAX']) ? '' : 'user';
		$projectController = $projectController===null ? new ProjectController($this->api, $this->request) : $projectController;
		$this->project = $projectController->getProject($this->request->id, $this->api->getUserId());
	}
	
	public function redirectProjectsIndex() {
		$response = new RedirectResponse( $this->api->linkToRoute('projects.project.index') );
		$response->setStatus(303);
		return $response;
	}
	
	public function getTasks($projectId=null) {
		if ($projectId === null) throw new \InvalidArgumentException("projectId not set");

		// get calendar events of type VTODO where calendar_id is projects_{projectId}
		$calendar_tasks = \OC_Calendar_Object::all("projects_$projectId");
		
		// for each todo, parse and return in array
		
		// return array
		return $calendar_tasks;
	}
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function index() {
		if ( !$this->project || !$this->project->canRead() ) return $this->redirectProjectsIndex();
		$this->params = array_merge($this->params, (array)$this->project);
		$this->params['tasks'] = (array)$this->getTasks( $this->project->getId() );
		var_dump($this->params['tasks']);
		return $this->render('tasks/index', $this->params, $this->renderas, array('X-PJAX-URL'=>$this->api->linkToRoute('projects.task.index', array('id'=>$this->project->getId()))));
	}
	
	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function create() {
		if ( !$this->project || !$this->project->canCreate() ) return $this->redirectProjectsIndex();
	}
	
	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function update() {
		if ( !$this->project || !$this->project->canUpdate() ) return $this->redirectProjectsIndex();
	}
	
	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function destroy() {
		if ( !$this->project || !$this->project->canDelete() ) return $this->redirectProjectsIndex();
	}
}

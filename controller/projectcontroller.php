<?php

namespace OCA\Projects\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\TemplateResponse;
//use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\RedirectResponse;
use \OCA\Projects\Core\API;
use \OCA\Projects\Db\Project;
use \OCA\Projects\Db\ProjectMapper;
/*
use \OCA\AppFramework\Http\JSONResponse;
use \Exception;
*/

class ProjectController extends Controller {
	
	private $params;
	private $renderas;
	private $projectMapper;
	
	public function __construct(API $api, Request $request, $projectMapper=null) {
		parent::__construct($api, $request);
		$this->params = array('requesttoken' => \OC_Util::callRegister() );
		$this->renderas = isset($_SERVER['HTTP_X_PJAX']) ? '' : 'user';
		$this->projectMapper = $projectMapper===null ? new ProjectMapper($this->api) : $projectMapper;
	}
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function index() {
		$this->params = array_merge($this->params, $this->getProjects($this->api->getUserId()));
        return $this->render('index', $this->params, $this->renderas);
	}
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function newForm() {
		return $this->render('new', $this->params, $this->renderas);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function create() {
		$project = $this->projectFromRequest($this->request);
		$project = $this->projectMapper->insert($project);
		$response = new RedirectResponse( $this->api->linkToRoute('projects.project.show', array('Id'=>$project->getId())) );
		$response->setStatus(303);
		return $response;
	}
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function show() {
		
	}

	public function projectFromRequest($request=null) {

		if ($request==null) Throw new \InvalidArgumentException("Request data not set");
		$project = new Project;
		// set all project properties from request
		return $project;
	}

	public function getProjects($uid=null) {
		if ($uid==null) Throw new \InvalidArgumentException("User Id Not specified");
		$userProjects = $this->projectMapper->getProjects($uid);
		$projects = array_merge($userProjects, $this->api->getItemsSharedWith('projects', 0));
		return $projects;
	}
	
	public function getParams() {
		return $this->params;
	}

	public function getRenderas() {
		return $this->renderas;
	}
	
}

<?php

namespace OCA\Projects\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\TemplateResponse;
use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\RedirectResponse;
use \OCA\Projects\Core\API;
use \OCA\Projects\Db\Project;
use \OCA\Projects\Db\ProjectMapper;

class ProjectController extends Controller {
	
	private $params;
	private $renderas;
	private $projectMapper;
	
	public function __construct(API $api, Request $request, $projectMapper=null) {
		parent::__construct($api, $request);
		$this->params = array('requesttoken' => \OC_Util::callRegister() );
		$this->renderas = isset($_SERVER['HTTP_X_PJAX']) ? '' : 'user';
		$this->api->addStyle('projects');
		$this->api->addScript('3rdparty/jquery.autosize.min');
		$this->api->addScript('3rdparty/jquery.pjax.min');
		$this->api->addScript('projects');
		$this->projectMapper = $projectMapper===null ? new ProjectMapper($this->api) : $projectMapper;
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
		$this->params = array_merge($this->params, array('projects'=>$this->getProjects($this->api->getUserId())));
        return $this->render('index', $this->params, $this->renderas, array('X-PJAX-URL'=>$this->api->linkToRoute('projects.project.index')));
		}
	
	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function create() {
		$uid = $this->api->getUserId();
		$project = $this->projectFromRequest($this->request);
		$project->setCreatedAt(date("Y-m-d H:i:s"));
		$project->setUid($uid);
		$this->projectMapper->insert($project);
		$this->params = array_merge($this->params, array('projects'=>$this->getProjects($uid)));
		return $this->render('index', $this->params, $this->renderas);
	}
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function show() {
		$project = $this->getProject($this->request->id, $this->api->getUserId());
		if (!$project || !$project->canRead() ) return $this->redirectProjectsIndex();
		$this->params = array_merge($this->params, (array)$project);
    	return $this->render('show', $this->params, $this->renderas);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function update() {
		$uid = $this->api->getUserId();
		$project = $this->getProject($this->request->id, $uid);
		if (!$project || !$project->canUpdate() ) return $this->redirectProjectsIndex();
		$project = $this->projectFromRequest($this->request);
		$project->setId($this->request->id);
		$project->setUpdatedAt(date("Y-m-d H:i:s"));
		$project->setModifiedBy($uid);
		$this->projectMapper->update($project);
		$this->params = array_merge($this->params, (array)$project);
    	return $this->render('show', $this->params, $this->renderas);
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function destroy() {
		$project = $this->getProject($this->request->id, $this->api->getUserId());
		if ($project && $project->canDelete() ) $this->projectMapper->delete($project);
		return $this->redirectProjectsIndex();
	}

	public function projectFromRequest($request=null) {
		if ($request===null) Throw new \InvalidArgumentException("Request data not set");
		$project = new Project;
		// set all project properties from request
		$project->setId($request->id);
		$project->setProjectName($request->projectName);
		return $project;
	}

	public function getProjects($uid=null) {
		if ($uid===null) Throw new \InvalidArgumentException("User id Not specified");
		$userProjects = $this->projectMapper->getProjects($uid);
		return array_merge((array)$userProjects, (array)$this->api->getItemsSharedWith('projects', 0));
	}
	
	public function getProject($id=null, $uid=null) {
		if ($id===null) Throw new \InvalidArgumentException("Project id Not specified");
		if ($uid===null) Throw new \InvalidArgumentException("User id Not specified");
		$project = $this->projectMapper->getProject($id, $uid);
		if (!$project) {
			$shared = $this->api->getItemSharedWith('projects', $id);
			$project = $this->projectMapper->findProjectById( $shared['item_source'], $uid, $shared['permissions'] );
		}
		return $project;
	}
	
	public function getParams() {
		return $this->params;
	}

	public function getRenderas() {
		return $this->renderas;
	}
	
}

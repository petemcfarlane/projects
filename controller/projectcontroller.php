<?php

namespace OCA\Projects\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\TemplateResponse;
use \OCA\AppFramework\Core\API;
use \OCA\AppFramework\Http\Request;
use \OCA\Projects\Db\ProjectMapper;
/*
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http\RedirectResponse;
use \OCA\SalesQuestionnaire\Db\Questionnaire;
use \Exception;
*/

class ProjectController extends Controller {
	
	private $params;
	private $renderas;
	
	public function __construct(API $api, Request $request) {
		parent::__construct($api, $request);
		$this->params = array('requesttoken' => \OC_Util::callRegister() );
		$this->renderas = isset($_SERVER['HTTP_X_PJAX']) ? '' : 'user';
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
		
	}
	public function getProjects($uid=null) {

		if ($uid==null) Throw new \InvalidArgumentException("User Id Not specified");
		$projectMapper = new ProjectMapper($this->api);
		$userProjects = $projectMapper->getProjects($uid);
		$projects = array_merge($userProjects, \OCP\Share::getItemsSharedWith('projects', 0));
		
		return $projects;
	}
	
	public function getParams() {
		return $this->params;
	}

	public function getRenderas() {
		return $this->renderas;
	}
	
}

<?php

namespace OCA\Projects\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\RedirectResponse;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\Projects\Core\API;
use \OCA\Projects\Db\Detail;
use \OCA\Projects\Db\DetailMapper;
use \OCA\Projects\Controller\ProjectController;

class DetailController extends Controller {
	
	private $params;
	private $renderas;
	private $detailMapper;
	private $project;
	
	public function __construct(API $api, Request $request, $detailMapper=null, $projectController=null) {
		parent::__construct($api, $request);
		$this->params = array('requesttoken' => \OC_Util::callRegister() );
		$this->params['image']['delete'] = $this->api->imagePath('actions/delete.svg');
		$this->renderas = isset($_SERVER['HTTP_X_PJAX']) ? '' : 'user';
		$this->detailMapper = $detailMapper===null ? new DetailMapper($this->api) : $detailMapper;
		$projectController = $projectController===null ? new ProjectController($this->api, $this->request) : $projectController;
		$this->project = $projectController->getProject($this->request->id, $this->api->getUserId());
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
		$this->params['details'] = $this->detailMapper->getDetails( $this->project->getId() );
		return $this->render('detail/index', $this->params, $this->renderas, array('X-PJAX-URL'=>$this->api->linkToRoute('projects.detail.index', array('id'=>$this->project->getId()))));
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function create() {
		if (!$this->project || !$this->project->canCreate() ) return $this->redirectProjectsIndex();
		if (!($this->request->detailKey)) throw new \InvalidArgumentException("detailKey not set");
		if (!($this->request->detailValue)) throw new \InvalidArgumentException("detailValue not set");
		if ( $detail=$this->detailMapper->getDetailFromKey( $this->project->getId(), $this->request->detailKey) ) return $this->redirectProjectsIndex();
		$detail = new Detail();
		$detail->setProjectId($this->project->getId());
		$detail->setDetailKey($this->request->detailKey);
		$detail->setDetailValue($this->request->detailValue);
		$detail = $this->detailMapper->insert( $detail );
		$this->params = array_merge($this->params, (array)$this->project);
		$this->params['details'] = $this->detailMapper->getDetails( $this->project->getId() );
		return $this->render('detail/index', $this->params, $this->renderas);
	}
	
	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function update() {
		if (!$this->project || !$this->project->canUpdate() ) return $this->redirectProjectsIndex();
		if (!($this->request->detailId)) throw new \InvalidArgumentException("detailId not set");
		if (!($this->request->detailValue)) throw new \InvalidArgumentException("detailValue not set");
		if (!$detail=$this->detailMapper->getDetail( $this->request->detailId) ) return $this->redirectProjectsIndex();
		$detail->setDetailValue($this->request->detailValue);
		$this->detailMapper->update($detail);
		$response = new RedirectResponse( $this->api->linkToRoute('projects.detail.index', array('id'=>$this->project->getId() ) ) );
		$response->setStatus(303);
		return $response;
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function destroy() {
		if (!$this->project || !$this->project->canDelete() ) return $this->redirectProjectsIndex();
		if (!($this->request->detailId)) throw new \InvalidArgumentException("detailId not set");
		$detail = $this->detailMapper->getDetail($this->request->detailId);
		if ($detail) $this->detailMapper->delete($detail);
		$response = new RedirectResponse( $this->api->linkToRoute('projects.detail.index', array('id'=>$this->project->getId())) );
		$response->setStatus(303);
		return $response;
	}
	
}

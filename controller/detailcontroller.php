<?php

namespace OCA\Projects\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\TemplateResponse;
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
		$this->renderas = isset($_SERVER['HTTP_X_PJAX']) ? '' : 'user';
		// $this->api->addStyle('projects');
		// $this->api->addScript('projects');
		$this->detailMapper = $detailMapper===null ? new DetailMapper($this->api) : $detailMapper;
		$projectController = $projectController===null ? new ProjectController($this->api, $this->request) : $projectController;
		$this->project = (array)$projectController->getProject($this->request->id, $this->api->getUserId());
	}
	
	public function getDetail($projectId=null, $detailKey=null) {
		if ($projectId===null) throw new \InvalidArgumentException('project_id not set');
		if ($detailKey===null) throw new \InvalidArgumentException('$detailKey not set');
		return $this->detailMapper->getDetail($projectId, $detailKey);
	}
	
	public function canRead($project) {
		if ( (isset($project['permissions']) && in_array('read', $project['permissions'])) || ($project && !isset($project['permissions'])) ) return true;
	}
	
	public function canCreate($project) {
		if ( (isset($project['permissions']) && in_array('create', $project['permissions'])) || ($project && !isset($project['permissions'])) ) return true;
	}
	
	public function canUpdate($project) {
		if ( (isset($project['permissions']) && in_array('update', $project['permissions'])) || ($project && !isset($project['permissions'])) ) return true;
	}
	
	public function canDelete($project) {
		if ( (isset($project['permissions']) && in_array('delete', $project['permissions'])) || ($project && !isset($project['permissions'])) ) return true;
	}
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function index() {
		if ( $this->canRead((array)$this->project) ) {
			$this->params = array_merge($this->params, (array)$this->project);
			$this->params['details'] = $this->detailMapper->getDetails($this->project['id']);
			return $this->render('detail/index', $this->params, $this->renderas);
        } else {
        	return new JSONResponse( array('error'=>'You do not have permissions to access this project'), 403 );
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function create() {
		if (!($this->project)) throw new \InvalidArgumentException("Project Id not set");
		if (!($this->request->detailKey)) throw new \InvalidArgumentException("Detail Key not set");
		if (!($this->request->detailValue)) throw new \InvalidArgumentException("Detail Value not set");
		if ( $this->canCreate((array)$this->project) ) {
			if ($detail=$this->getDetail($this->project['id'], $this->request->detailKey)) {
				$detail->setDetailValue($this->request->detailValue);
				$this->detailMapper->update($detail);
			} else {
				$detail = new Detail();
				$detail->setProjectId($this->project['id']);
				$detail->setDetailKey($this->request->detailKey);
				$detail->setDetailValue($this->request->detailValue);
				$detail = $this->detailMapper->insert( $detail );
			}
			$this->params = array_merge($this->params, (array)$this->project);
			$this->params['details'] = $this->detailMapper->getDetails($this->project['id']);
			return $this->render('detail/index', $this->params, $this->renderas);
		} else {
			return new JSONResponse( array('error'=>'You do not have permissions to create details for this project'), 403);
		}
	}
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function show() {
		if (!($this->request->detailKey)) throw new \InvalidArgumentException("Detail Key not set");
		if ( $this->canRead($this->project) && $detail=$this->getDetail($this->project['id'], $this->request->detailKey)) {
			$this->params = array_merge($this->params, (array)$this->project);
			$this->params['detail'] = (array)$detail;
			return $this->render('detail/show', $this->params, $this->renderas);
		}
		return new JSONResponse( array('error'=>'You do not have permissions to access this project'), 403 ); 	
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function update() {
		if (!($this->project)) throw new \InvalidArgumentException("Project Id not set");
		if (!($this->request->detailKey)) throw new \InvalidArgumentException("Detail Key not set");
		if (!($this->request->detailValue)) throw new \InvalidArgumentException("Detail Value not set");
		if ($this->canUpdate((array)$this->project)) {
			$detail=$this->getDetail($this->project['id'], $this->request->detailKey);
			$detail->setDetailValue($this->request->detailValue);
			$this->detailMapper->update( $detail );
			$this->params = array_merge($this->params, (array)$this->project);
			$this->params['detail'] = (array)$detail;
			$response = new RedirectResponse( $this->api->linkToRoute('projects.detail.index', array('id'=>$this->project['id']) ) );
			$response->setStatus(303);
			return $response;
		} else {
			return new JSONResponse( array('error'=>'You do not have permissions to update details for this project'), 403);
		}
	}

	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function destroy() {
		if (!($this->project)) throw new \InvalidArgumentException("Project Id not set");
		if (!($this->request->detailKey)) throw new \InvalidArgumentException("Detail Key not set");
		if ( $this->canDelete($this->project) ) {
			$detail = $this->getDetail($this->project['id'], $this->request->detailKey);
			$this->detailMapper->delete( $detail );
			$response = new RedirectResponse( $this->api->linkToRoute('projects.detail.index', array('id'=>$this->project['id']) ) );
			$response->setStatus(303);
			return $response;
		} else {
			return new JSONResponse( array('error'=>'You do not have permissions to update details for this project'), 403);
		}
	}
	
	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function delete() {
		if (!($this->project)) throw new \InvalidArgumentException("Project Id not set");
		if (!($this->request->detailKey)) throw new \InvalidArgumentException("Detail Key not set");
		if ( $this->canDelete($this->project) && $detail=$this->getDetail($this->project['id'], $this->request->detailKey)) {
			$this->params = array_merge($this->params, (array)$this->project);
			$this->params['detail'] = (array)$detail;
			return $this->render('detail/delete', $this->params, $this->renderas);
		} else {
			return new JSONResponse( array('error'=>'You do not have permissions to update details for this project'), 403);
		}
	}
	
}

<?php
namespace OCA\Projects\Db;

use \OCA\AppFramework\Db\Mapper;
use \OCA\AppFramework\Core\API;
use \OCA\Projects\Db\Project;

class ProjectMapper extends Mapper {


    public function __construct(API $api) {
      parent::__construct($api, 'projects');
    }

	protected function findAllRows($sql, $params, $limit=null, $offset=null) {
		$result = $this->execute($sql, $params, $limit, $offset);
		$projects = array();
		while($row = $result->fetchRow()){
			$project = new Project($row);
			array_push($projects, $project);
		}
		return $projects;
	}

	public function getProjects($uid) {
		$sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `uid` = ?';
        $projects = $this->findAllRows($sql, array($uid));
        return $projects;
	}
	
	public function getProject($id, $uid) {
		$sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `id` = ? AND `uid` = ?';
		$result = $this->execute($sql, array($id, $uid) );
		$row = $result->fetchRow();
		return ($row === null || $row === false) ? null : new Project($row, $uid);
	}
	
	public function findProjectById($id, $uid, $permissions=null) {
		$sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `id` = ?';
		$result = $this->execute($sql, array($id) );
		$row = $result->fetchRow();
		return ($row === null || $row === false) ? null : new Project($row, $uid, $permissions);
	}
/*

	public function findById($Id) {
		$sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `id` = ?';
		$row = $this->findOneQuery($sql, array($Id) );
		$questionnaire = new Questionnaire($row);
		return $questionnaire;
	}

	public function findByIdAndUser($Id, $uid) {
		$sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `id` = ? AND `uid` = ?';
		$row = $this->findOneQuery($sql, array($Id, $uid) );
		$questionnaire = new Questionnaire($row);
		return $questionnaire;
	}
	
	public function indexByIdAndUser($Id, $uid) {
		$sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `id` = ? AND `uid` = ?';
		$row = $this->findOneQuery($sql, array($Id, $uid) );
		$questionnaire = new Questionnaire($row);
		return $questionnaire;
	}

	public function findQuestionnaires() {
        $sql = 'SELECT `id`, `customer`, `created_at`, `updated_at`, `uid`, `modified_by`, `project_name`, `platform`, `territories`, `oem` FROM `' . $this->getTableName() . '`';
        $questionnaires = $this->findAllRows($sql, array());
        return $questionnaires;
	}
	
	public function getUserQuestionnaires($uid) {
        $sql = 'SELECT `id`, `customer`, `created_at`, `updated_at`, `uid`, `modified_by`, `project_name`, `platform`, `territories`, `oem` FROM `' . $this->getTableName() . '` WHERE `uid` = ?';
        $questionnaires = $this->findAllRows($sql, array($uid));
        return $questionnaires;
	}
	
	public function searchUserQuestionnaires($uid, $search) {
        $sql = 'SELECT `id`, `customer`, `created_at`, `updated_at`, `uid`, `modified_by`, `project_name`, `platform`, `territories`, `oem` FROM `' . $this->getTableName() . '` WHERE `uid` = ? AND '
        	. '(`customer` LIKE "%'.$search.'%" '
        	. 'OR `project_name` LIKE "%'.$search.'%" '
        	. 'OR `uid` LIKE "%'.$search.'%" '
        	. 'OR `platform` LIKE "%'.$search.'%" '
        	. 'OR `territories` LIKE "%'.$search.'%" '
        	. 'OR `oem` LIKE "%'.$search.'%")';
        $questionnaires = $this->findAllRows($sql, array($uid));
        return $questionnaires;
	}
	*/


}

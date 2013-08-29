<?php
namespace OCA\Projects\Db;

use \OCA\AppFramework\Db\Mapper;
use \OCA\AppFramework\Core\API;


class ProjectMapper extends Mapper {


    public function __construct(API $api) {
      parent::__construct($api, 'projects');
    }

	public function getProjects($uid) {
		return array('project1', 'project2', 'project3');
	}
/*
	protected function findAllRows($sql, $params, $limit=null, $offset=null) {
		$result = $this->execute($sql, $params, $limit, $offset);
		$questionnaires = array();
		while($row = $result->fetchRow()){
			$questionnaire = new Questionnaire($row);
			array_push($questionnaires, $questionnaire);
		}
		return $questionnaires;
	}

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

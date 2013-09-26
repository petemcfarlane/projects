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

}

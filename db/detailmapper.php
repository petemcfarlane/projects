<?php
namespace OCA\Projects\Db;

use \OCA\AppFramework\Db\Mapper;
use \OCA\AppFramework\Core\API;
use \OCA\Projects\Db\Detail;

class DetailMapper extends Mapper {

    public function __construct(API $api) {
      parent::__construct($api, 'projects_details');
    }
	
	protected function findAllRows($sql, $params, $limit=null, $offset=null) {
		$result = $this->execute($sql, $params, $limit, $offset);
		$details = array();
		while($row = $result->fetchRow()){
			$detail = new Detail($row);
			array_push($details, $detail);
		}
		return $details;
	}

	public function getDetails($projectId=null) {
		if ($projectId===null) throw new \InvalidArgumentException('$projectId must be set');
		$sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `project_id` = ?';
		$params = array($projectId);
        $details = $this->findAllRows($sql, array($projectId));
        return $details;
	}
	
	public function getDetail($projectId, $detailKey) {
		$sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `project_id` = ? AND `detail_key` = ?';
		$params = array($projectId, $detailKey);
		$result = $this->execute($sql, $params);
		$row = $result->fetchRow();
		if ($row) return new Detail($row);
	}
}

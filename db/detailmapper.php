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

	public function getDetails($projectId) {
		$sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `project_id` = ?';
		$params = array($projectId);
        $details = $this->findAllRows($sql, array($projectId));
        return $details;
	}
	
	public function getDetailFromKey($projectId, $detailKey) {
		$sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `project_id` = ? AND `detail_key` = ?';
		$params = array($projectId, $detailKey);
		$result = $this->execute($sql, $params);
		$row = $result->fetchRow();
		return ($row === null || $row === false) ? null : new Detail($row);
	}

	public function getDetail($id) {
		$sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `id` = ?';
		$result = $this->execute($sql, array($id) );
		$row = $result->fetchRow();
		return ($row === null || $row === false) ? null : new Detail($row);
	}
}

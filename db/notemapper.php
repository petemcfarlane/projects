<?php
namespace OCA\Projects\Db;

use \OCA\AppFramework\Db\Mapper;
use \OCA\AppFramework\Core\API;
use \OCA\Projects\Db\Note;

class NoteMapper extends Mapper {

    public function __construct(API $api) {
      parent::__construct($api, 'projects_notes');
    }

	protected function findAllRows($sql, $params, $limit=null, $offset=null) {
		$result = $this->execute($sql, $params, $limit, $offset);
		$notes = array();
		while ($row = $result->fetchRow()) {
			$note = new Note($row);
			array_push($notes, $note);
		}
		return $notes;
	}

	public function getNotes($projectId=null) {
		if ($projectId===null) throw new \InvalidArgumentException('$projectId must be set');
		$sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `project_id` = ?';
		$params = array($projectId);
        $notes = $this->findAllRows($sql, array($projectId));
        return $notes;
	}
/*	
	public function getDetail($projectId, $detailKey) {
		$sql = 'SELECT * FROM `' . $this->getTableName() . '` WHERE `project_id` = ? AND `detail_key` = ?';
		$params = array($projectId, $detailKey);
		$result = $this->execute($sql, $params);
		$row = $result->fetchRow();
		if ($row) return new Detail($row);
	}
*/
}

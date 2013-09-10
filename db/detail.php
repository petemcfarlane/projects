<?php
namespace OCA\Projects\Db;

use \OCA\AppFramework\Db\Entity;

class Detail extends Entity {

	public $projectId;
	public $detailKey;
	public $detailValue;

	public function __construct($row=null){
		if ($row) $this->fromRow($row);
	}
	
}

<?php
namespace OCA\Projects\Db;

use \OCA\AppFramework\Db\Entity;

class Note extends Entity {

	public $projectId;
	public $note;

	public function __construct($row=null){
		if ($row) $this->fromRow($row);
	}
	
}

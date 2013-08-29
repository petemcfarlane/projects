<?php
namespace OCA\Projects\Db;

use \OCA\AppFramework\Db\Entity;

class Project extends Entity {

	public $createdAt;
	public $uid;
	public $updatedAt;
	public $modifiedBy;
	public $projectName;
	public $projectType;
	public $platform;

	public function __construct($fromRow=null){
		if ($fromRow) $this->fromRow($fromRow);
	}
	
}

<?php
namespace OCA\Projects\Db;

use \OCA\AppFramework\Db\Entity;

class Project extends Entity {

	public $createdAt;
	public $uid;
	public $updatedAt;
	public $modifiedBy;
	public $name;
	public $calendarId;
	protected $user;
	protected $permissions;

	public function __construct($fromRow=null, $user=null, $permissions=null){
		if ($fromRow) $this->fromRow($fromRow);
		$this->user = $user;
		$this->permissions = $permissions;
	}
	
	public function canCreate() {
		if ($this->uid === $this->user || $this->permissions & \OCP\PERMISSION_CREATE ) return true;
	}
	
	public function canRead() {
		if ($this->uid === $this->user || $this->permissions & \OCP\PERMISSION_READ ) return true;
	}
	
	public function canUpdate() {
		if ($this->uid === $this->user || $this->permissions & \OCP\PERMISSION_UPDATE ) return true;
	}
	
	public function canDelete() {
		if ($this->uid === $this->user || $this->permissions & \OCP\PERMISSION_DELETE ) return true;
	}
	
	public function canShare() {
		if ($this->uid === $this->user || $this->permissions & \OCP\PERMISSION_SHARE ) return true;
	}
	
	
}

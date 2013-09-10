<?php
namespace OCA\Projects\Lib\Share;

class ShareProject implements \OCP\Share_Backend {
	
	private $project;
	
	const FORMAT_PROJECT = 0;

	/**
	 * Transform a database columnname to a property 
	 * @param string $columnName the name of the column
	 * @return string the property name
	 */

	public function columnToProperty($columnName){
		$parts = explode('_', $columnName);
		$property = null;

		foreach($parts as $part){
			if($property === null){
				$property = $part;
			} else {
				$property .= ucfirst($part);
			}
		}

		return $property;
	}

	
	public function isValidSource($itemSource, $uidOwner) {
		$query  = \OCP\DB::prepare('SELECT * FROM `*PREFIX*projects` WHERE `id` = ? AND `uid` = ?');
		$result = $query->execute( array($itemSource, $uidOwner) );
		$this->project = $result->fetchRow();
		if ($this->project) return true;
		return false;
	}

	public function generateTarget($itemSource, $shareWith, $exclude = null) {
		return $project['id'];
	}

	public function formatItems($items, $format, $parameters = null) {
		$projects = array();
		if ($format !== self::FORMAT_PROJECT) return null;
		foreach ($items as $item) {
			$query = \OCP\DB::prepare('SELECT * FROM `*PREFIX*projects` WHERE `id` = ?');
			$result = $query->execute( array($item['item_source']));
			$row = $result->fetchRow();
			if ($row) {
				$project = array();
				foreach ($row as $property => $value) {
					$newProperty = self::columnToProperty($property);
					$project[$newProperty] = $value;
				}
				if ($item['permissions'] & \OCP\PERMISSION_CREATE) $project['permissions'][] = "create";
				if ($item['permissions'] & \OCP\PERMISSION_READ)   $project['permissions'][] = "read";
				if ($item['permissions'] & \OCP\PERMISSION_UPDATE) $project['permissions'][] = "update";
				if ($item['permissions'] & \OCP\PERMISSION_DELETE) $project['permissions'][] = "delete";
				if ($item['permissions'] & \OCP\PERMISSION_SHARE)  $project['permissions'][] = "share";
				$project['uid_owner'] = $item['uid_owner'];
				$project['displayname_owner'] = $item['displayname_owner'];
				$projects[] = $project;
			}
		}
		return $projects;
		/*
		$salesquestionnaires = array();
				if ($format == self::FORMAT_QUESTIONNAIRE) {
					foreach ($items as $item) {
						if (isset($parameters['search'])) {
							$search = $parameters['search'];
							$query = \OCP\DB::prepare('SELECT `id`, `customer`, `created_at`, `updated_at`, `uid`, `modified_by`, `project_name`, `platform`, `territories`, `oem` FROM `*PREFIX*salesquestionnaire` WHERE `id` = ? AND'
								. '(`customer` LIKE "%'.$search.'%" '
								. 'OR `project_name` LIKE "%'.$search.'%" '
								. 'OR `uid` LIKE "%'.$search.'%" '
								. 'OR `platform` LIKE "%'.$search.'%" '
								. 'OR `territories` LIKE "%'.$search.'%" '
								. 'OR `oem` LIKE "%'.$search.'%")');
						} else {
							$query = \OCP\DB::prepare('SELECT * FROM `*PREFIX*salesquestionnaire` WHERE `id` = ?');
						}
						$result = $query->execute( array($item['item_source']) );
						$row = $result->fetchRow();
						if ($row) {
							$salesquestionnaire = array();
							foreach ($row as $property => $value) {
								$newProperty = self::columnToProperty($property);
								$salesquestionnaire[$newProperty] = $value;
							}
							if ($item['permissions'] & \OCP\PERMISSION_CREATE) $salesquestionnaire['permissions'][] = "CREATE";
							if ($item['permissions'] & \OCP\PERMISSION_READ)   $salesquestionnaire['permissions'][] = "READ";
							if ($item['permissions'] & \OCP\PERMISSION_UPDATE) $salesquestionnaire['permissions'][] = "UPDATE";
							if ($item['permissions'] & \OCP\PERMISSION_DELETE) $salesquestionnaire['permissions'][] = "DELETE";
							if ($item['permissions'] & \OCP\PERMISSION_SHARE)  $salesquestionnaire['permissions'][] = "SHARE";
							$salesquestionnaire['uid_owner'] = $item['uid_owner'];
							$salesquestionnaire['displayname_owner'] = $item['displayname_owner'];
							$salesquestionnaires[] = $salesquestionnaire;
						}
					}
				}
				return $salesquestionnaires;*/
		
	}

}

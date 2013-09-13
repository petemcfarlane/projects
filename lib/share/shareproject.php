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





// NEEDS LOOKING INTO!!!




		return ($itemSource);
		// return $project['id'];
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
				$project['permissions'] = $item['permissions'];
				$project['uid_owner'] = $item['uid_owner'];
				$project['displayname_owner'] = $item['displayname_owner'];
				$projects[] = $project;
			}
		}
		return $projects;
	}

}

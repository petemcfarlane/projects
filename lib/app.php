<?php
Class OC_Projects_App {
	
	public static function getProjects() {
		$query 	  = OC_DB::prepare('SELECT id, name, description FROM *PREFIX*projects WHERE users LIKE ?');
		$result	  = $query->execute( array( "%" . OC_User::getUser() . "%" ) );
		return $result->fetchAll();
	}
	
	public static function getProject($id) {
		$query   = OC_DB::prepare('SELECT * FROM *PREFIX*projects WHERE id = ?');
		$result = $query->execute( array ( $id ) );
		return $result->fetchRow();
	}
	
	public static function getProjectName($id) {
		$query = OC_DB::prepare("SELECT name FROM *PREFIX*projects WHERE id = ?");
		$result = $query->execute( array( $id ) );
		$data = $result->fetchRow() ;
		return $data['name'];
	}

	public static function getProjectCreator($id) {
		$query = OC_DB::prepare("SELECT creator FROM *PREFIX*projects WHERE id = ?");
		$result = $query->execute( array( $id ) );
		$data = $result->fetchRow() ;
		return $data['creator'];
	}

	public static function newProject($request) {
		$query = OCP\DB::prepare('INSERT INTO *PREFIX*projects (name, description, users, creator) VALUES (?, ?, ?, ?)');
		$query->execute( array ( $request['name'], $request['description'], $request['users'], OC_User::getUser() ) );
		$id = OC_DB::insertid();
		
		$query = OCP\DB::prepare('INSERT INTO *PREFIX*projects_actions (project_id, uid, uaction, target_type, target_id, atime) VALUES (?, ?, ?, ?, ?, ?)');
		$query->execute( array ( $id, OC_User::getUser(), "new_project", "project", $id, date("Y-m-d H:i:s") ) );
		return $id;
	}
	
	public static function actionDetail ($uid, $uaction, $target_type, $target_id, $atime) {
		$return = $uid." ";
		switch($uaction) {
			case "new_project":
				$return .= "created the project ";
				break;
			case "edit_project";
				$return .= "edited the project ";
				break;
		}
		switch ($target_type) {
			case "project":
				$return .= "<a href=" . OCP\Util::linkTo( 'projects', 'index.php' ) . "/" . $target_id .">" . self::getProjectName($target_id) . "</a>";
				break;
			case "platform":
				$return .= "platform <a href=" . OCP\Util::linkTo( 'projects', 'index.php' ) . "/" . $target_id .">" . self::getProjectName($target_id) . "</a>";
				break;
			case "project_type":
				$return .= "type <a href=" . OCP\Util::linkTo( 'projects', 'index.php' ) . "/" . $target_id .">" . self::getProjectName($target_id) . "</a>";
				break;
		}
		$return .= ", " . date( "h:ia", strtotime($atime) ) . " " . date( "jS M Y", strtotime($atime) );
		return $return;
	}

	public static function userInProject($project_id) {
		$query = OCP\DB::prepare("SELECT users FROM *PREFIX*projects WHERE id = ?");
		$result = $query->execute( array($project_id) );
		$result = $result->fetchRow();
		if ( strpos( $result['users'], OC_User::getUser() ."," ) !== false ) {
			return true;
		} else {
			return false;
		}
	}

	public static function updateProject($data) {
		
		if ( !self::userInProject($data['id']) ) {
			return array("Error" => OC_User::getUser() . " not in project " . $data['id']);
			exit;
		}
		
		$updateQuery = OCP\DB::prepare("UPDATE *PREFIX*projects SET $data[key] = ? WHERE id = ?");
		$updateQuery->execute ( array ( $data['value'], $data['id'] ) );
		self::updateAction($data['id'], "edit_project", $data['key'], $data['id']);
		return array( "id" => $data['id'], "data" => array($data['key'] => $data['value']), "modified" => date("Y-m-d H:i:s"), "modifier" => OC_User::getUser() );
	}
		
	public static function updateAction( $project_id=NULL, $action="edit_project", $target_type, $target_id ) {
		$query = OCP\DB::prepare('INSERT INTO *PREFIX*projects_actions (project_id, uid, uaction, target_type, target_id, atime) VALUES (?, ?, ?, ?, ?, ?)');
		$query->execute( array ( $project_id, OC_User::getUser(), $action, $target_type, $target_id, date("Y-m-d H:i:s") ) );
		return OC_DB::insertid();
	}

	public static function newTask($request) {
		$vcalendar = new OC_VObject('VCALENDAR');
		$vcalendar->add('PRODID', 'Projects Sontia Cloud');
		$vcalendar->add('VERSION', '2.0');

		$vtodo = new OC_VObject('VTODO');
		$vcalendar->add($vtodo);

		$vtodo->setDateTime('CREATED', 'now', Sabre\VObject\Property\DateTime::UTC);

		$vtodo->setUID();
		return self::updateVCalendarFromRequest($request, $vcalendar);
	}

	public static function arrayForJSON($id, $vtodo, $user_timezone) {
		$task = array( 'id' => $id );
		$task['summary'] = $vtodo->getAsString('SUMMARY');
		$task['description'] = $vtodo->getAsString('DESCRIPTION');
		$task['location'] = $vtodo->getAsString('LOCATION');
		$task['categories'] = $vtodo->getAsArray('CATEGORIES');
		$task['completed_by'] = $vtodo->getAsString('X-COMPLETED-BY');
		$task['assigned_to'] = $vtodo->getAsString('X-ASSIGNED-TO');
		$due = $vtodo->DUE;
		if ($due) {
			$task['due_date_only'] = $due->getDateType() == Sabre\VObject\Property\DateTime::DATE;
			$due = $due->getDateTime();
			$due->setTimezone(new DateTimeZone($user_timezone));
			$task['due'] = $due->format('U');
		}
		else {
			$task['due'] = false;
		}
		$task['priority'] = $vtodo->getAsString('PRIORITY');
		$completed = $vtodo->COMPLETED;
		if ($completed) {
			$completed = $completed->getDateTime();
			$completed->setTimezone(new DateTimeZone($user_timezone));
			$task['completed'] = $completed->format('Y-m-d H:i:s');
		}
		else {
			$task['completed'] = false;
		}
		$task['complete'] = $vtodo->getAsString('PERCENT-COMPLETE');
		return $task;
	}

	public static function createVCalendarFromRequest($request) {
		$vcalendar = new OC_VObject('VCALENDAR');
		$vcalendar->add('PRODID', 'Sontia Cloud Projects');
		$vcalendar->add('VERSION', '2.0');
		
		$vtodo = new OC_VObject('VTODO');
		$vcalendar->add($vtodo);
		
		$vtodo->setDateTime('CREATED', 'now', Sabre\VObject\Property\DateTime::UTC);
		
		$vtodo->setUID();
		return self::updateVCalendarFromRequest($request, $vcalendar);
	}

	public static function updateVCalendarFromRequest($request, $vcalendar) {
		$summary = $request['summary'];
		$categories = $request["categories"];
		$priority = $request['priority'];
		$percent_complete = $request['percent_complete'];
		$completed = $request['completed'];
		$location = $request['location'];
		$due = $request['due'];
		$description = $request['description'];
		$assigned = $request['assigned'];
		
		$vtodo = $vcalendar->VTODO;
		
		$vtodo->setDateTime('LAST-MODIFIED', 'now', Sabre\VObject\Property\DateTime::UTC);
		$vtodo->setDateTime('DTSTAMP', 'now', Sabre\VObject\Property\DateTime::UTC);
		$vtodo->setString('SUMMARY', $summary);
		
		$vtodo->setString('LOCATION', $location);
		$vtodo->setString('DESCRIPTION', $description);
		$vtodo->setString('CATEGORIES', $categories);
		$vtodo->setString('PRIORITY', $priority);
		$vtodo->setString('X-ASSIGNED-TO', $assigned);

		if ($due) {
			$timezone = OC_Calendar_App::getTimezone();
			$timezone = new DateTimeZone($timezone);
			$due = new DateTime($due, $timezone);
			$vtodo->setDateTime('DUE', $due);
		} else {
			unset($vtodo->DUE);
		}

		self::setComplete($vtodo, $percent_complete, $completed);

		return $vcalendar;
	}

	public static function setComplete($vtodo, $percent_complete, $completed) {
		if (!empty($percent_complete)) {
			$vtodo->setString('PERCENT-COMPLETE', $percent_complete);
			$vtodo->setString('STATUS', 'NEEDS-ACTION');
		}else{
			$vtodo->setString('PERCENT-COMPLETE', $percent_complete);
			//$vtodo->__unset('PERCENT-COMPLETE');
			$vtodo->__unset('X-COMPLETED-BY');
			$vtodo->setString('STATUS', 'IN-PROCESS');
		}

		if ($percent_complete == 100) {
			if (!$completed) {
				$completed = 'now';
				$vtodo->setString('STATUS', 'COMPLETED');
			}
		} else {
			$completed = null;
		}
		if ($completed) {
			$timezone = OC_Calendar_App::getTimezone();
			$timezone = new DateTimeZone($timezone);
			$completed = new DateTime($completed, $timezone);
			$vtodo->setDateTime('COMPLETED', $completed);
			$vtodo->setString('STATUS', "COMPLETED");
			$vtodo->setString('X-COMPLETED-BY', OC_User::getUser());
			// Unnecessary?
			// OCP\Util::emitHook('OC_Task', 'taskCompleted', $vtodo);
		} else {
			unset($vtodo->COMPLETED);
			$vtodo->__unset('X-COMPLETED-BY');
			$vtodo->setString('STATUS', 'IN-PROCESS');
		}
	}

	/*
	 * Notes
	 */

	public static function newNote($project_id, $note) {
		$creator = OC_User::getUser();
		$atime = date("Y-m-d H:i:s");
		$query = OCP\DB::prepare('INSERT INTO *PREFIX*projects_notes (project_id, parent_id, creator, status, atime, note) VALUES (?, ?, ?, ?, ?, ?)');
		$query->execute( array ( $project_id, NULL, $creator, 'current', $atime, $note ) );
		$note_id = OC_DB::insertid();
		return(array("note_id" => $note_id, "project_id" => $project_id, "creator" => $creator, "status" => "current", "atime" => $atime, "note" => $note));
	}

	public static function trashNote($trash_note_id) {
		$updateNote = OCP\DB::prepare("UPDATE *PREFIX*projects_notes SET status = ? WHERE note_id = ?");
		$updateNote->execute ( array ( 'old', $trash_note_id ) );
		
		$query = OCP\DB::prepare( 'SELECT project_id, note FROM `*PREFIX*projects_notes` WHERE `note_id` = ?' );
		$result = $query->execute(array($trash_note_id));
		$note = $result->fetchRow();
		
		$creator = OC_User::getUser();
		$atime = date("Y-m-d H:i:s");
		
		$query = OCP\DB::prepare('INSERT INTO *PREFIX*projects_notes (project_id, parent_id, creator, status, atime, note) VALUES (?, ?, ?, ?, ?, ?)');
		$query->execute( array ( $note['project_id'], NULL, $creator, 'trash', $atime, $note['note'] ) );
		$note_id = OC_DB::insertid();
		return(array("note_id" => $note_id, "project_id" => $note['project_id'], "creator" => $creator, "status" => "trash", "atime" => $atime, "note" => $note['note']));
		
	}

	public static function editNote($project_id, $note) {
		
	}

}
?>
<?php
Class OC_Projects_App {
	
	/**
	 * @brief return an array of active projects (id, name, description) where the user is a partipant
	 * @param string $uid
	 * @return array
	 */
	public static function getProjects($uid) {
		$query 	  = OC_DB::prepare('SELECT id, name, description, status FROM *PREFIX*projects WHERE users LIKE ? AND status != 5');
		$result	  = $query->execute( array( "%$uid%" ) );
		return $result->fetchAll();
	}
	
	/**
	 * @brief return an array of archived projects (id, name, description) where the user is a partipant
	 * @param string $uid
	 * @return array
	 */
	public static function getArchivedProjects($uid) {
		$query 	  = OC_DB::prepare('SELECT id, name, description FROM *PREFIX*projects WHERE users LIKE ? AND status = 5');
		$result	  = $query->execute( array( "%$uid%" ) );
		return $result->fetchAll();
	}
	
	/**
	 * @brief returns full details of a single projects as an array where the user is a partipant
	 * @param number $id project_id
	 * @param string $uid userid
	 * @return array
	 */
	public static function getProject($id, $uid) {
		$query  = OC_DB::prepare('SELECT * FROM *PREFIX*projects WHERE id = ? AND users LIKE ? AND status != 5');
		$result = $query->execute( array ( $id, "%$uid%" ) );
		return $result->fetchRow();
	}
	
	/**
	 * @brief Return the project name
	 * @param number $id project_id
	 * @return string
	 */
	public static function getProjectName($id) {
		$query = OC_DB::prepare("SELECT name FROM *PREFIX*projects WHERE id = ?");
		$result = $query->execute( array( $id ) );
		$data = $result->fetchRow() ;
		return $data['name'];
	}

	/**
	 * @brief Return the project creator uid
	 * @param number $id project_id
	 * @return string
	 */
	public static function getProjectCreator($id) {
		$query = OC_DB::prepare("SELECT creator FROM *PREFIX*projects WHERE id = ?");
		$result = $query->execute( array( $id ) );
		$data = $result->fetchRow() ;
		return $data['creator'];
	}

	/**
	 * @brief Create a new project from $request, return new project_id
	 * @param array $request array(name, description, users)
	 * @return number project id
	 */
	public static function newProject($request) {
		$query = OCP\DB::prepare('INSERT INTO *PREFIX*projects (name, description, users, creator) VALUES (?, ?, ?, ?)');
		$query->execute( array ( $request['name'], $request['description'], $request['users'], $request['creator'] ) );
		$id = OC_DB::insertid();
		self::addAction( $id, "created", "project", $id, $request['name'] );
		return $id;
	}
	
	/**
	 * @brief Update a project
	 * @param array $data array(project_id, update_key, update_value)
	 * @param string $action default="edited"
	 * @param string $excerpt default=NULL
	 * @return string
	 */
	public static function updateProject($data, $action="edited", $excerpt=NULL) {
		$project_id = $data['project_id'];
		$update_key = mysql_real_escape_string($data['update_key']);
		$update_value = $data['update_value'];
		$updateQuery = OCP\DB::prepare("UPDATE *PREFIX*projects SET $update_key = ? WHERE id = ?");
		$updateQuery->execute ( array ( $update_value, $project_id ) );
		self::addAction( $project_id, $action, 'project', $project_id, $excerpt);
		return array("project_id"=>$project_id, "update"=>$update_key, "value"=>$update_value);
	}

	/**
	 * @brief Check if the user is participant in a project
	 * @param number $project_id
	 * @param string $uid default=current user
	 * @return bool
	 */
	public static function userInProject($project_id, $uid=NULL) {
		if (!$uid) $uid=OC_User::getUser();
		$query = OCP\DB::prepare("SELECT users FROM *PREFIX*projects WHERE id = ?");
		$result = $query->execute( array($project_id) );
		$result = $result->fetchRow();
		if ( strpos( $result['users'], $uid ."," ) !== false ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @brief Returns the id of a newly created action.
	 * @param number $project_id 
	 * @param string $action (past tense)
	 * @param string $target_type e.g. project, task, note, person
	 * @param string $target_id 
	 * @param string $excerpt to display the first 100 characters of item
	 * @param string $atime time of action, default = now
	 * @return number
	 */
	public static function addAction( $project_id, $action, $target_type, $target_id, $excerpt=NULL, $uid=NULL, $atime=NULL ){
		if (!$project_id || !is_numeric($project_id) ) throw new Exception("Error \$project_id required and must be numeric", 1);
		if (!$action) throw new Exception("Error \$action is required");
		if (!$target_type) throw new Exception("Error \$target_type is required");
		if (!$target_id) throw new Exception("Error \$target_id is required");
		if (!isset ($uid)) $uid = OC_User::getUser();
		if (!isset ($atime)) $atime = date("Y-m-d H:i:s");
		$query = OCP\DB::prepare('INSERT INTO *PREFIX*projects_actions (project_id, uid, uaction, target_type, target_id, atime, excerpt) VALUES (?, ?, ?, ?, ?, ?, ?)');
		$query->execute( array ( $project_id, $uid, $action, $target_type, $target_id, $atime, $excerpt ) );
		return OC_DB::insertid();
	}

	/**
	 * @brief Returns a humanly readable text with link to item, thumbnail & time
	 * @param number $action_id
	 * @return string html article with image, user, link, etc
	 */
	public static function readAction( $action_id ) {
		$query = OCP\DB::prepare('SELECT * FROM *PREFIX*projects_actions WHERE id = ?');
		$result = $query->execute(array($action_id));
		$action = $result->fetchRow();
		$return = "<article class='project_action' data-action_id='$action[id]'>";
		$return .= "<span class='event_time'>" . date("j M, g:ia", strtotime($action["atime"]) ) . "</span>";
		$return .= 	"<img class='thumbnail_40' src='" .OCP\Util::linkTo( 'user_photo', 'index.php' ) . "/photo/" . $action['uid'] . "/40' />";
		$return .= OC_User::getDisplayName($action['uid']) . " ";
		switch ($action['uaction']) {
			default : $return .= " " .$action['uaction'] . " "; break;
		}
		switch ($action['target_type']) {
			case ("project") : $return .= " the project: <em class='excerpt'>" . $action['excerpt'] . "</em>"; break;
			case ("person") : $return .= " ".OC_User::getDisplayName($action['target_id']); break;
			case ("note") : $return .= " a note: <em class='excerpt'>" . $action['excerpt'] . "</em>"; break;
		}
		$return .= "</article>";
		return $return;
	}
	
	
	/*
	 * Tasks
	 */
	 
	/**
	 * @brief Creates a new task
	 * @param array $request()
	 */ 
	/*public static function newTask($request) {
		$vcalendar = new OC_VObject('VCALENDAR');
		$vcalendar->add('PRODID', 'Projects Sontia Cloud');
		$vcalendar->add('VERSION', '2.0');

		$vtodo = new OC_VObject('VTODO');
		$vcalendar->add($vtodo);

		$vtodo->setDateTime('CREATED', 'now', Sabre\VObject\Property\DateTime::UTC);

		$vtodo->setUID();
		return self::updateVCalendarFromRequest($request, $vcalendar);
	}*/

	/**
	 * @brief converts priority number to exclamation marks
	 * @param number $priority
	 * @return string !!!, !!, ! or ''
	 */
	public static function getPriority($priority) {
		if ($priority > 6 && $priority <= 9) { // low
			return('!');
		} elseif ($priority > 3 && $priority < 7) { // medium
			return('!!');
		} elseif ($priority >= 1 && $priority < 4) { // high
			return('!!!');
		} 
	}
	
	
	/**
	 * @brief returns a task as an array
	 * @param number
	 * @param number
	 * @param string
	 * @return array
	 */
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

	/**
	 * @brief return vcalendar
	 * @param array
	 * @return object
	 */
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

	/**
	 * @brief mark a task as complete
	 * @param number $vtodo task id
	 * @param number $percent_complete 
	 * @param number $completed time completed
	 * @return 
	 */
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
		} else {
			unset($vtodo->COMPLETED);
			$vtodo->__unset('X-COMPLETED-BY');
			$vtodo->setString('STATUS', 'IN-PROCESS');
		}
	}

	/*
	 * Notes
	 */

	/**
	 * @brief creates a note
	 * @param number $project_id
	 * @param string $note
	 * @return array 
	 */
	public static function newNote($project_id, $note) {
		$creator = OC_User::getUser();
		$atime = date("Y-m-d H:i:s");
		$query = OCP\DB::prepare('INSERT INTO *PREFIX*projects_notes (project_id, parent_id, creator, status, atime, note) VALUES (?, ?, ?, ?, ?, ?)');
		$query->execute( array ( $project_id, NULL, $creator, 'current', $atime, $note ) );
		$note_id = OC_DB::insertid();
		self::addAction( $project_id, "created", "note", $note_id, substr($note, 0, 100) ) ;
		return(array("note_id" => $note_id, "project_id" => $project_id, "creator" => $creator, "status" => "current", "atime" => $atime, "note" => $note));
	}

	/**
	 * @brief Create a new note marked as trash
	 * @param number $trash_note_id
	 * @return array 
	 */
	public static function trashNote($trash_note_id) {
		$updateNote = OCP\DB::prepare("UPDATE *PREFIX*projects_notes SET status = 'old' WHERE note_id = ?");
		$updateNote->execute ( array ( $trash_note_id ) );
		
		$query = OCP\DB::prepare( 'SELECT project_id, note FROM `*PREFIX*projects_notes` WHERE `note_id` = ?' );
		$result = $query->execute(array($trash_note_id));
		$notedata = $result->fetchRow();
		$project_id = $notedata['project_id'];
		$note = $notedata['note'];
		$creator = OC_User::getUser();
		$atime = date("Y-m-d H:i:s");
		
		$query = OCP\DB::prepare('INSERT INTO *PREFIX*projects_notes (project_id, parent_id, creator, status, atime, note) VALUES (?, ?, ?, "trash", ?, ?)');
		$query->execute( array ( $project_id, $trash_note_id, $creator, $atime, $note ) );
		$note_id = OC_DB::insertid();
		self::addAction( $project_id, "trashed", "note", $note_id, substr($note, 0, 100) );
		return(array("note_id" => $note_id, "project_id" => $project_id, "creator" => $creator, "status" => "trash", "atime" => $atime, "note" => $note));
	}

	/**
	 * @brief Create a new note with the changes made
	 * @param number $edit_note_id
	 * @param string $note
	 * @return array (new note);
	 */
	public static function editNote($edit_note_id, $note) {
		$updateNote = OCP\DB::prepare("UPDATE *PREFIX*projects_notes SET status = 'old' WHERE note_id = ?");
		$updateNote->execute( array ( $edit_note_id ) );
		
		$query = OCP\DB::prepare( 'SELECT project_id FROM `*PREFIX*projects_notes` WHERE `note_id` = ?' );
		$result = $query->execute(array($edit_note_id));
		$notedata = $result->fetchRow();
		$project_id = $notedata['project_id'];
		$creator = OC_User::getUser();
		$atime = date("Y-m-d H:i:s");
		
		$query = OCP\DB::prepare('INSERT INTO *PREFIX*projects_notes (project_id, parent_id, creator, status, atime, note) VALUES (?, ?, ?, "current", ?, ?)');
		$query->execute( array ( $project_id, $edit_note_id, $creator, $atime, $note ) );
		$note_id = OC_DB::insertid();
		self::addAction( $project_id, "edited", "note", $note_id, substr($note, 0, 100) );
		return(array("note_id" => $note_id, "project_id" => $project_id, "creator" => $creator, "status" => "current", "atime" => $atime, "note" => $note ));
	}

	/**
	 * @brief move a trashed note back to a current note
	 * @param number $restore_note_id
	 * @return array 
	 */
	public static function restoreNote($restore_note_id) {
		$updateNote = OCP\DB::prepare("UPDATE *PREFIX*projects_notes SET status = 'current' WHERE note_id = ?");
		$updateNote->execute( array ( $restore_note_id ) );
		
		$query = OCP\DB::prepare( 'SELECT project_id, creator, atime, note FROM `*PREFIX*projects_notes` WHERE `note_id` = ?' );
		$result = $query->execute(array($restore_note_id));
		$notedata = $result->fetchRow();
		$project_id = $notedata['project_id'];
		$creator = $notedata['creator'];
		$atime = $notedata['atime'];
		$note = $notedata['note'];
		
		self::addAction( $project_id, "restored", "note", $restore_note_id, substr($note, 0, 100) );
		return(array("note_id" => $restore_note_id, "project_id" => $project_id, "creator" => $creator, "status" => "current", "atime" => $atime, "note" => $note ));
	}

	/**
	 * @brief Delete a note and all it's revisions from database
	 * @param number $delete_note_permenantly
	 * @return array notes deleted
	 */
	public static function deleteNotePermenantly($delete_note_permenantly) {
		$query = OCP\DB::prepare( 'SELECT project_id FROM `*PREFIX*projects_notes` WHERE `note_id` = ?' );
		$result = $query->execute(array($delete_note_permenantly));
		$notedata = $result->fetchRow();
		$project_id = $notedata['project_id'];
		self::addAction( $project_id, "deleted", "note", $delete_note_permenantly);
		$to_delete = array($delete_note_permenantly);

		// get parent notes
		while ( $delete_note_permenantly ) {
			$query = OCP\DB::prepare( 'SELECT parent_id FROM *PREFIX*projects_notes WHERE note_id = ?' );
			$result = $query->execute( array ( $delete_note_permenantly ) );
			$notedata = $result->fetchRow();
			$delete_notes = OCP\DB::prepare("DELETE FROM *PREFIX*projects_notes WHERE note_id = ?");
			$delete_notes->execute( array ( $delete_note_permenantly ) );
			if ($notedata['parent_id']) {
				$delete_note_permenantly = $notedata['parent_id'];
				array_push( $to_delete, $delete_note_permenantly );
			} else {
				$delete_note_permenantly = false;
			}
		}
		return (array ('note_id' => $to_delete ));
	}
	
	/*
	 * People
	 */

	/**
	 * @brief toggle person belonging to project, and can share/unshare calendar, if exists
	 * @param string $uid
	 * @param number $project_id
	 * @return array 
	 */
	public static function togglePerson($uid, $project_id) {
		$query = OCP\DB::prepare( "SELECT users, calendar_id FROM *PREFIX*projects WHERE id = ?");
		$result = $query->execute( array($project_id) );
		$result = $result->fetchRow();
		$users = explode(',', $result['users'], -1);
		if (($key = array_search($uid, $users)) !== false) {
			unset($users[$key]);
			$users = implode(',', $users) . ",";
			$current_user = false;
			$action = "removed";
			if ($result['calendar_id'] != 0 ) OCP\Share::unshare("calendar", $result['calendar_id'], 0, $uid);
		} else {
			$users[] = $uid;
			$users = implode(',', $users) . ",";
			$current_user = true;
			$action = "invited";
			if ($result['calendar_id'] > 0 ) OCP\Share::shareItem("calendar", $result['calendar_id'], 0, $uid, 31);
		}
		$query = OCP\DB::prepare( "UPDATE *PREFIX*projects SET users = ? WHERE id = ?");
		$query->execute( array($users, $project_id) );
		self::addAction( $project_id, $action, "person", $uid );
		return array("uid"=>$uid, "project_id"=>$project_id, "current_user" => $current_user, "users"=>$users);
	}
}
?>
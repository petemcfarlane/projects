<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');
OCP\JSON::callCheck();

// If task is completed or uncompleted
if ( isset($_POST['type']) && $_POST['type'] == 'complete') {
	$vcalendar = OC_Calendar_App::getVCalendar( $_POST['id'] );
	$vtodo = $vcalendar->VTODO;
	OC_Projects_App::setComplete($vtodo, $_POST['checked'], null);
	if ( OC_Calendar_Object::edit($_POST['id'], $vcalendar->serialize()) ) {
		$updateAction = OC_Projects_App::updateAction( $_POST['project_id'], $action = $_POST['checked'] ? "task_completed" : "task_uncompleted", "task", $_POST['id'] );
		$task_info = OC_Projects_App::arrayForJSON($_POST['id'], $vtodo, OC_Calendar_App::getTimezone());
		OCP\JSON::success(array('data' => $task_info, 'updateAction' => $updateAction));
	}
	exit;
}

// delete task
if ( isset($_POST['type']) && $_POST['type'] == 'delete') {
	$task = OC_Calendar_App::getEventObject( $_POST['id'] );
	OC_Calendar_Object::delete($_POST['id']);
	OCP\JSON::success(array('data' => array( 'id' => $_POST['id'] )));
	exit;
}
// update task
if ( isset($_POST['type']) && $_POST['type'] == 'update') {
	$vcalendar = OC_Calendar_App::getVCalendar( $_POST['id'] );
	$vtodo = $vcalendar->VTODO;
	$vtodo->setString('SUMMARY', $_POST['summary']);
	$vtodo->setString('X-ASSIGNED-TO', $_POST['assign']);
	$vtodo->setString('PRIORITY', $_POST['priority']);
	$vtodo->setString('DESCRIPTION', $_POST['notes']);
	if ($_POST['due']) {
		try {
			$timezone = OC_Calendar_App::getTimezone();
			$timezone = new DateTimeZone($timezone);
			$due = "";
			$due = new DateTime('@'. strtotime($_POST['due']));
			$due->setTimezone($timezone);
			$type = Sabre\VObject\Property\DateTime::LOCALTZ;
		} catch (Exception $e) {
			OCP\JSON::error(array('data'=>array('message'=>OC_Task_App::$l10n->t('Invalid date/time'))));
			exit();
		}
	$vtodo->setDateTime('DUE', $due, $type);
	}else {
		unset($vtodo->DUE);
	}
	if ( OC_Calendar_Object::edit($_POST['id'], $vcalendar->serialize()) ) {
		$task_info = OC_Projects_App::arrayForJSON($_POST['id'], $vtodo, OC_Calendar_App::getTimezone());
		OCP\JSON::success(array('data' => $task_info));
	}
	exit;
}

// If no calendar exists for the project yet
if ( $_POST['calendar_id'] === '0' ){
	// Make the calendar, owned by project creator
	try {
		$project_id = $_POST['project_id'];
		$uid = OC_Projects_App::getProjectCreator($project_id);
		$name = OC_Projects_App::getProjectName($project_id);
		$components = 'VEVENT,VTODO,VJOURNAL';
		$timezone = NULL;
		$order = 0;
		$rand_color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
		$new_calendar_id = OC_Calendar_Calendar::addCalendar($uid,$name,$components,$timezone,$order,$rand_color);
	} catch (Exception $e) {
		throw new Exception( 'Failed to create calendar ['. OC_Projects_App::getProjectName($_POST['project)id']).'] ' . $e);
		print json_encode($e->getMessage());
	}
	
	// Share the calendar, full privs, with all users.
	try {
		$token = OCP\Share::shareItem("calendar", $new_calendar_id, 0, "Chris", 31);
	} catch (Exception $e) {
		throw new Exception( 'Failed to share calendar ['. OC_Projects_App::getProjectName($_POST['project_id']).'] ' . $e);
		print json_encode($e->getMessage());
	}

	// Update the project with the calendar ID
	$data['id'] = $_POST['project_id'];
	$data['key'] = "calendar_id";
	$data['value'] = $new_calendar_id;
	OC_Projects_App::updateProject($data);
	
	// Return the new calendar ID to the page to update the form 'calendar_id' value, so no more calendars will be created
	//OCP\JSON::success(array("calendar_id" => $new_calendar_id));
	$_POST['calendar_id'] = $new_calendar_id;
}

// Once we have the calendar and the user has shared permissions, create the task
if ( isset($_POST['project_id']) && $_POST['project_id'] !== '' ) {
	$request = array();
	$request['summary'] = $_POST['summary'];
	$request["categories"] = null;
	$request['priority'] = $_POST['priority'];
	$request['percent_complete'] = null;
	$request['completed'] = null;
	$request['location'] = null;
	$request['due'] = $_POST['duedate'];
	$request['description'] = $_POST['description'];
	$request['assigned'] = $_POST['assigned'];
	$calendar_id = $_POST['calendar_id'];
	$vcalendar = OC_Projects_App::createVCalendarFromRequest($request);
	$id = OC_Calendar_Object::add($calendar_id, $vcalendar->serialize());
	
	$user_timezone = OC_Calendar_App::getTimezone();
	$task = OC_Projects_App::arrayForJSON($id, $vcalendar->VTODO, $user_timezone);
	
	OCP\JSON::success(array('task' => $task, 'calendar_id' => $calendar_id ));
}
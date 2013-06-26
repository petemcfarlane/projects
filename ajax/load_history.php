<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');
OCP\JSON::callCheck();

if (isset($_POST['project_id']) && isset($_POST['offset'] )) {

	$query 	 = OC_DB::prepare("SELECT id FROM *PREFIX*projects_actions WHERE project_id = ? ORDER BY atime DESC LIMIT 20 OFFSET $_POST[offset]");
	$result	 = $query->execute( array( $_POST['project_id'] ) );
	$actions = $result->fetchAll();

	foreach ($actions as $action) {
		print( OC_Projects_App::readAction($action['id']) );
	}

}
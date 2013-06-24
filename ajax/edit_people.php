<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');
OCP\JSON::callCheck();

if(isset($_POST['toggle_uid']) && isset($_POST['project_id'])) {
	try {
		$status = OC_Projects_App::togglePerson($_POST['toggle_uid'], $_POST['project_id']);
		OCP\JSON::success($status);
	} catch (Exception $e) {
		OCP\JSON::error(array('Error'=> "Error toggleing person"));
		exit;
	}
}
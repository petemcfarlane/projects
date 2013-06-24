<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');
OCP\JSON::callCheck();

// New project
if ( isset ( $_POST['new_project'] ) ) {

	$request = array();
	$request['name'] = $_POST['name'];
	$request['description'] = $_POST['description'];
	$creator = OC_User::getUser();
	$request['creator'] = $creator;
	$request['users'] = "$creator,";
	foreach ($_POST['users'] as $user ) {
		if ($user != '') {
			$request['users'] .= $user.',';
		}
	}
	try {
		$project_id = OC_Projects_App::newProject($request);
	} catch (Exception $e) {
		OCP\JSON::error(array('data'=>array('message'=>'Failed to create project: ' . $e )));
		exit();
	}
	OCP\JSON::success(array("project_id" => $project_id));
	exit;


} elseif ( isset($_POST['archive_project']) && is_numeric($_POST['archive_project'])) {
// Archive Project

	//check user has perms
	if (!OC_Projects_App::userInProject($_POST['archive_project']) ) {
		OCP\JSON::error(array('data'=>array('message'=>"Error, user not in project!" )));
		exit;
	}
	//archive_project
	$data['project_id']=$_POST['archive_project'];
	$data['update_key']='status';
	$data['update_value']=5;
	$response = OC_Projects_App::updateProject($data, "archived", OC_Projects_App::getProjectName($data['project_id']) );
	OCP\JSON::success(array("archived_project" => $response));
	exit;
	

} elseif ( isset($_POST['restore_archived_project']) && is_numeric($_POST['restore_archived_project'])) {
// Restore Archived Project

	//check user has perms
	if (!OC_Projects_App::userInProject($_POST['restore_archived_project']) ) {
		OCP\JSON::error(array('data'=>array('message'=>"Error, user not in project!" )));
		exit;
	}
	// un-archive_project
	$data['project_id']=$_POST['restore_archived_project'];
	$data['update_key']='status';
	$data['update_value']=1;
	$response = OC_Projects_App::updateProject($data, "restored", OC_Projects_App::getProjectName($data['project_id']) );
	OCP\JSON::success(array("restored_project" => $response));
	exit;
	

} elseif ( isset($_POST['project_id']) && $_POST['project_id'] !== '' ) {
// Update Project
	$data['project_id'] = $_POST['project_id'];
	foreach($_POST as $key => $value) {
		if ($key !== 'project_id') {
			$data["update_key"] = $key;
			$data["update_value"] = $value;
		}
	}
	$response = OC_Projects_App::updateProject($data, "edited", "$data[update_key] => $data[update_value]");
	OCP\JSON::success(array("restored_project" => $response));
}
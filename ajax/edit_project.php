<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');
OCP\JSON::callCheck();

// New project
if ( isset ( $_POST['new_project'] ) ) {

	$request = array();
	$request['name'] = $_POST['name'];
	$request['description'] = $_POST['description'];
	$users = array();
	$request['users'] = "";
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
	$data['update_key']='archive';
	$data['update_value']=1;
	$response = OC_Projects_App::updateProject($data);
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
	$data['update_key']='archive';
	$data['update_value']=0;
	$response = OC_Projects_App::updateProject($data);
	OCP\JSON::success(array("restored_project" => $response));
	exit;
	

} elseif ( isset($_POST['id']) && $_POST['id'] !== '' ) {
// Update Project
	$data['id'] = $_POST['id'];
	foreach($_POST as $key => $value) {
		if ($key !== 'id') {
			$data["key"] = $key;
			$data["value"] = $value;
		}
	}

	$response = OC_Projects_App::updateProject($data);
	
	print json_encode($response);
	
}
<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');
OCP\JSON::callCheck();

if ( isset($_POST['project_id']) && $_POST['project_id'] !== '' ) {
// Update Project
	$data['project_id'] = $_POST['project_id'];
	foreach($_POST as $key => $value) {
		if ($key !== 'project_id') {
			$data["update_key"] = str_replace("_", " ", $key);
			$data["update_value"] = $value;
		}
	}
	if ($data['update_key'] == "name" || $data['update_key'] == "description") {
		$response = OC_Projects_App::updateProject($data, "set", strtolower($data["update_key"]) . " to \"$data[update_value]\"" );
	} else {
		$response = OC_Projects_App::updateProjectMeta($data, "set", strtolower($data["update_key"]) . " to \"$data[update_value]\"" );
	}
	OCP\JSON::success($response);
}
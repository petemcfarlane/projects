<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');
OCP\JSON::callCheck();


if ( isset($_POST['id']) && $_POST['id'] !== '' ) {

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
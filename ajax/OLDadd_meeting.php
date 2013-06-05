<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');
OCP\JSON::callCheck();


if ( isset($_POST['id']) && $_POST['id'] !== '' ) {

	$response = OC_Projects_App::addMeeting($_POST);

	print json_encode($response);

}
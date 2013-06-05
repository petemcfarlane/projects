<?php
OCP\User::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');

OCP\Util::addScript('projects','projects');
OCP\Util::addScript('3rdparty','jquery.pjax');
OCP\Util::addStyle('projects', 'projects');

OCP\App::setActiveNavigationEntry( 'projects' );

if ( isset ($_POST['add_project']) ) {
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
	$projectID = OC_Projects_App::newProject($request);
}

if ( isset ( $params['projectID']) ) $projectID = $params['projectID']; 
if ( isset ($_GET['id'])) $projectID = $_GET['id'];

if ( isset($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX'] == true ) {
	// JUST load the content
	$renderas = '';
} else $renderas = 'user';

$tmpl = new OCP\Template( 'projects', 'projects', $renderas );
if ( isset($projectID) ) $tmpl->assign( 'id', $projectID );
if ( isset($params['view'])) $tmpl->assign( 'view', $params['view'] );
$tmpl->printPage();
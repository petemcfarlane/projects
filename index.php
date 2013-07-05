<?php
OCP\User::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');

OCP\Util::addScript('projects','projects');
OCP\Util::addScript('3rdparty','jquery.pjax.min');
OCP\Util::addStyle('projects', 'projects');

OCP\App::setActiveNavigationEntry( 'projects' );

$project_id = ( isset($params['project_id']) && is_numeric($params['project_id']) ) ? $params['project_id'] : NULL; 
$view 		= isset($params['view']) ? $params['view'] : ( isset($project_id) ? 'project' : NULL );
$item		= isset($params['item']) ? $params['item'] : NULL;
$uid		= OC_User::getUser();
$renderas 	= ( isset($_SERVER['HTTP_X_PJAX']) ) ? '' : 'user';

$tmpl = new OCP\Template( 'projects', 'projects', $renderas );
$tmpl->assign( 'project_id', $project_id );
$tmpl->assign( 'view', 		 $view );
$tmpl->assign( 'item', 	 	 $item );
$tmpl->assign( 'uid', 		 $uid );
$tmpl->printPage();
<?php
namespace OCA\Projects;

if ( !\OCP\App::isEnabled('appframework') ) {
	 \OCP\Util::writeLog('projects', "App Framework app must be enabled", \OCP\Util::ERROR); 
	 exit;
}

$api = new \OCA\AppFramework\Core\API('projects');

$api->addNavigationEntry(array(
	'id' => $api->getAppName(),
	'order' => 10,
	'href' => $api->linkToRoute('projects.project.index'),
	'icon' => $api->imagePath('projects.svg'),
	'name' => "Projects"
)); 

\OCP\Share::registerBackend('projects', '\OCA\Projects\Lib\Share\ShareProject');
// \OC_Search::registerProvider('\OCA\SalesQuestionnaire\Lib\SearchProvider');
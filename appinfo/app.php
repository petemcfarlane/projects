<?php
OC::$CLASSPATH['OC_Projects_App'] = 'apps/projects/lib/app.php';

OCP\App::addNavigationEntry( array( 
	'id' => 'projects',
	'order' => 74,
	'href' => OCP\Util::linkTo( 'projects', 'index.php' ),
	'icon' => OCP\Util::imagePath( 'projects', 'projects.svg' ),
	'name' => 'Projects'
));
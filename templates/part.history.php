<?php 
	// eventually, print actions for project(id)
	
	//OC_Projects_App::listActions($project['id']);
	
	// but for now:
	
	$query 	 = OC_DB::prepare('SELECT id FROM *PREFIX*projects_actions WHERE project_id = ? ORDER BY atime DESC');
	$result	 = $query->execute( array( $project['id'] ) );
	$actions = $result->fetchAll();

	foreach ($actions as $action) {
		print_unescaped( OC_Projects_App::readAction($action['id']) );
	}
?>
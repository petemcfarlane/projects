<?php 
	$query 	 = OC_DB::prepare('SELECT uid, uaction, target_type, target_id, atime FROM *PREFIX*projects_actions WHERE project_id = ? ORDER BY atime DESC');
	$result	 = $query->execute( array( $project['id'] ) );
	$actions = $result->fetchAll();

	foreach ($actions as $action) {
		print_unescaped( "<p>" . OC_Projects_App::actionDetail($action['uid'], $action['uaction'], $action['target_type'], $action['target_id'], $action['atime']) . "</p>");
	}
?>
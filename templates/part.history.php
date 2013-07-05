<?php 
$query 	 = OC_DB::prepare('SELECT id FROM *PREFIX*projects_actions WHERE project_id = ? ORDER BY atime DESC LIMIT 20');
$result	 = $query->execute( array( $project['id'] ) );
$actions = $result->fetchAll();

foreach ($actions as $action) {
	print_unescaped( OC_Projects_App::readAction($action['id']) );
}
?>
<button id="load_more_events" data-project_id="<?php p($project['id']); ?>" data-offset="20"><i class="icon-download"></i> Load more events</button>
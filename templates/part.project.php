<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/history" ); ?>" class="project_preview">
	<h2>Latest updates</h2>
	<?php 
	$query 	 = OC_DB::prepare('SELECT id FROM *PREFIX*projects_actions WHERE project_id = ? ORDER BY atime DESC LIMIT 3');
	$result	 = $query->execute( array( $project['id'] ) );
	$actions = $result->fetchAll();

	foreach ($actions as $action) {
		print_unescaped( OC_Projects_App::readAction($action['id']) );
	}
	?>
</a>

<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/details" ); ?>">
	<h2>Details</h2>
</a>

<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/tasks" ); ?>">Tasks</a>

<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/notes" ); ?>">Notes</a>

<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/people" ); ?>">People</a>

<a id='archive_project' data-project_id='<?php p($project['id']); ?>'>Archive Project</a>
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

<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/details" ); ?>" class="project_preview">
	<h2>Details</h2>
</a>

<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/tasks" ); ?>" class="project_preview">
	<h2>Tasks</h2>
</a>

<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/notes" ); ?>" class="project_preview">
	<h2>Notes</h2>
</a>

<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/people" ); ?>" class="project_preview">
	<h2>People</h2>
</a>

<a id='archive_project' data-project_id='<?php p($project['id']); ?>' class="project_preview">
	<h2>Archive Project</h2>
</a>

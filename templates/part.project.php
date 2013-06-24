<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/history" ); ?>" class="tag">History</a>
<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/details" ); ?>" class="tag">Details</a>
<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/tasks" ); ?>" class="tag">Tasks</a>
<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/notes" ); ?>" class="tag">Notes</a>
<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/people" ); ?>" class="tag">People</a>
<a id='archive_project' data-project_id='<?php p($project['id']); ?>' class="tag">Archive Project</a>
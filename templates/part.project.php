<div class="sub-section">
	<h2>Latest Updates</h2>
	<?php $query = OC_DB::prepare('SELECT * FROM *PREFIX*projects_actions WHERE project_id = ? ORDER BY atime DESC LIMIT 3');
	$result	 = $query->execute( array( $project['id'] ) );
	$actions = $result->fetchAll();
	foreach ($actions as $action) {
	//	print_unescaped( "<p>" . OC_Projects_App::actionDetail($action['uid'], $action['uaction'], $action['target_type'], $action['target_id'], $action['atime']) . "</p>");
	} ?>
	<a href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/history"; ?>">History</a>
</div>

<div class="sub-section">
	<a href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/details"; ?>">Details</a>
</div>

<div class="sub-section">
	<a href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/tasks"; ?>">Tasks</a>
</div>

<div class="sub-section">
	<a href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/notes"; ?>">Notes</a>
</div>

<div class="sub-section">
	<a href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/files"; ?>">Files</a>
</div>

<div class="sub-section">
	<a href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/meetings"; ?>">Meetings</a>
</div>

<div class="sub-section">
	<a href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/contacts"; ?>">Contacts</a>
</div>

<div class="sub-section">
	<a href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/emails"; ?>">Emails</a>
</div>

<div class="sub-section">
	<a href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/events"; ?>">Events</a>
</div>

<div class="sub-section">
	<a href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id'] . "/issues"; ?>">Issues</a>
</div>

<a id='archive_project' data-project_id='<?php p($project['id']); ?>'>Archive Project</a>
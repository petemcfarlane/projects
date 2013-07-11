<button id="new_project_button"><i class="icon-plus"></i> New Project</button>

<ul class="project_list">
	<?php foreach ($projects as $project) { ?>
		<li>
			<a href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id']; ?>" class="<?php switch($project['status']) {
						case 1:
							p('status-in_progress');
							break;
						case 2:
							p('status-hold');
							break;
						case 3:
							p('status-issue');
							break;
						case 4:
							p('status-complete');
							break;
						case 5:
							p('status-archived');
							break;
						} ?>">
			<h2><?php p($project['name']); ?></h2>
			<span class="description"><?php p($project['description']); ?></span>
			</a>
		</li>
	<?php } ?>
</ul>

<div id="show_archived_projects_container"><button id="show_archived_projects"><i class="icon-trash"></i> Show Trash</button></div>

<?php $archived_projects = OC_Projects_App::getArchivedProjects($uid); ?>
<ul class="project_list hidden" id="archived_project_list">
	<?php foreach ($archived_projects as $project) { ?>
		<li><a class='restore_archived_project' data-project_id="<?php p($project['id']); ?>"><h2><?php p($project['name']); ?></h2>Restore</a></li>
	<?php } ?>
</ul>

<form method="post" action="projects">
	<input type="submit" name="test" value="Val" />
</form>

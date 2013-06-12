<?php $projects = OC_Projects_App::getProjects($uid); ?>
<ul id="project_list">
	<?php foreach ($projects as $project) { ?>
		<li><a href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id']; ?>"><?php p($project['name']); ?></a></li>
	<?php } ?>
</ul>

<button id="new_project_button">Add New</button>

<button id="show_archived_projects">Show Archived Projects</button>

<?php $archived_projects = OC_Projects_App::getArchivedProjects($uid); ?>
<ul id="archived_project_list" class="hidden">
	<?php foreach ($archived_projects as $project) { ?>
		<li><?php p($project['name']); ?> - <a class='restore_archived_project' data-project_id="<?php p($project['id']); ?>">Restore</a></li>
	<?php } ?>
</ul>
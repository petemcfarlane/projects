<ul id="project_list">
	<?php foreach ($projects as $project) { ?>
		<li><a href="<?php print OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/" . $project['id']; ?>"><?php p($project['name']); ?></a></li>
	<?php } ?>
</ul>

<button id="new_project_button">Add New</button>
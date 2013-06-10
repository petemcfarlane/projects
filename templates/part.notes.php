<?php // Add new note ?>
<button id="new_note_button">New note</button>
<article id="note_template" class="note">
	<div class="content"></div>
	<footer>
		<span class="meta"></span>
		<button class="edit_button" data-note_id="">Edit</button>
		<button class="trash_button" data-note_id="">Trash</button>
	</footer>
</article>

<form id="new_note">
	<input type="hidden" name="project_id" value="<?php print $project['id']; ?>" />
	<p>
		<label for="note" method="post">Note:</label>
		<textarea id="note" name="note"></textarea>
	</p>
	<p>
		<input type="submit" value="Add this note" /> or <a id="cancel_new_note">cancel this note</a>
	</p>
</form>

<?php // Load all 'current' notes 
	$query = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*projects_notes` WHERE `project_id` = ? AND status = "current" ORDER BY atime DESC' );
	$result = $query->execute(array($project['id']));
	$notes = $result->fetchAll();

	// List notes 
	foreach ($notes as $note) { ?>
		<article id="note_id_<?php p($note['note_id']); ?>" class="note">
			<div class="content"><?php print_unescaped($note['note']); ?></div>
			<footer>
				<span class="meta"><?php p($note['parent_id'] ? 'Updated by ' : 'Created by ' ); p($note['creator']); p(' on '); p( date("jS M, 'y \t H:i", strtotime($note['atime'])) ); ?></span>
				<button class="edit_button" data-note_id="<?php p($note['note_id']);?>">Edit</button>
				<button class="trash_button" data-note_id="<?php p($note['note_id']);?>">Trash</button>
			</footer>
		</article>


	<?php } ?>
	<button id='show_trash'>Show Trash</button>
		<article id="trash_template" class="note trash">
			<div class="content"></div>
			<footer><span class="meta"></span><button class="restore" data-note_id="">Restore</button><button class="delete_permenantly" data-note_id="">Delete permenantly</button></footer>
		</article>
	
	<?php // Load all 'trashed' notes 
	$query = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*projects_notes` WHERE `project_id` = ? AND status = "trash" ORDER BY atime DESC' );
	$result = $query->execute(array($project['id']));
	$trashed_notes = $result->fetchAll();
	
	// List notes 
	foreach ($trashed_notes as $note) { ?>
		<article id="note_id_<?php p($note['note_id']); ?>" class="note trash">
			<div class="content"><?php print_unescaped($note['note']); ?></div>
			<footer>
				<span class="meta"><?php p( 'Trashed by ' ); p($note['creator']); p(' on '); p( date("jS M, 'y \t H:i", strtotime($note['atime'])) ); ?></span>
				<button class="restore" data-note_id="<?php p($note['note_id']);?>">Restore</button>
				<button class="delete_permenantly" data-note_id="<?php p($note['note_id']);?>">Delete permenantly</button>
			</footer>
		</article>


	<?php }  ?>


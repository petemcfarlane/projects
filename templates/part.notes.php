<?php // Add new note ?>
<button id="new_note_button"><i class="icon-plus"></i> New note</button>

<form id="new_note">
	<input type="hidden" name="project_id" value="<?php print $project['id']; ?>" />
	<p>
		<label for="note" class="hidden">Note:</label>
		<textarea id="note" name="note" placeholder="Note..."></textarea>
	</p>
	<p>
		<input type="submit" value="Add this note" /> or <a id="cancel_new_note">cancel this note</a>
	</p>
</form>

<ul class="notes-list">
	<li id="note_template" class="note">
		<div class="content"></div>
		<footer>
			<span class="meta"></span>
			<button class="edit_button"><i class="icon-pencil"></i> Edit</button>
			<button class="trash_button"><i class="icon-trash"></i> Trash</button>
		</footer>
	</li>

	<?php // Load all 'current' notes 
		$query = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*projects_notes` WHERE `project_id` = ? AND status = "current" ORDER BY atime DESC' );
		$result = $query->execute(array($project['id']));
		$notes = $result->fetchAll();
	
		// List notes 
		foreach ($notes as $note) { ?>
			<li id="note_id_<?php p($note['id']); ?>" class="note">
				<div class="content"><?php print_unescaped($note['note']); ?></div>
				<footer>
					<span class="meta"><?php p($note['parent_id'] ? 'Updated by ' : 'Created by ' ); p($note['creator']); p(' on '); p( date("jS M, 'y \t H:i", strtotime($note['atime'])) ); ?></span>
					<button class="edit_button" data-note_id="<?php p($note['id']);?>"><i class="icon-pencil"></i> Edit</button>
					<button class="trash_button" data-note_id="<?php p($note['id']);?>"><i class="icon-trash"></i> Trash</button>
				</footer>
			</li>
	
	
	<?php } ?>
</ul>

<div id="show_trash_notes_container"><button id='show_trash_notes'><i class="icon-trash"></i> Show Trash</button></div>

<ul id="trash_notes_list" class="hidden">
	<li id="trash_template" class="note trash">
		<div class="content"></div>
		<footer><span class="meta"></span><button class="restore">Restore</button><button class="delete_permenantly">Delete permenantly</button></footer>
	</li>
	
	<?php // Load all 'trashed' notes 
	$query = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*projects_notes` WHERE `project_id` = ? AND status = "trash" ORDER BY atime DESC' );
	$result = $query->execute(array($project['id']));
	$trashed_notes = $result->fetchAll();
	
	// List notes 
	foreach ($trashed_notes as $note) { ?>
		<li id="note_id_<?php p($note['id']); ?>" class="note trash">
			<div class="content"><?php print_unescaped($note['note']); ?></div>
			<footer>
				<span class="meta"><?php p( 'Trashed by ' ); p($note['creator']); p(' on '); p( date("jS M, 'y \t H:i", strtotime($note['atime'])) ); ?></span>
				<button class="restore" data-note_id="<?php p($note['id']);?>">Restore</button>
				<button class="delete_permenantly" data-note_id="<?php p($note['id']);?>">Delete permenantly</button>
			</footer>
		</li>
	<?php } ?>
</ul>
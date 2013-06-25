<?php 
// MUST ENABLE CALENDARS APP
if (!OCP\App::isEnabled('calendar')) {
	print_unescaped('<ul><li class="error">');
	p($l->t('The calendar application also need to be enabled to use this application.'));
	print_unescaped('</li></ul>');
	exit;
}

// Check for existing calendar
$calendar_id = $project['calendar_id'];
$calendar = OC_Calendar_Calendar::find($calendar_id);
date_default_timezone_set(OC_Calendar_App::getTimezone()); 

$query = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_objects` WHERE `calendarid` = ? AND objecttype = "VTODO"' );
$result = $query->execute(array($calendar_id));
$calendar_tasks = $result->fetchAll();

$tasks = array();
foreach( $calendar_tasks as $task ) {
	if(is_null($task['summary'])) {
		continue;
	}
	$object = OC_VObject::parse($task['calendardata']);
	$vtodo = $object->VTODO;
	try {
		$tasks[] = OC_Projects_App::arrayForJSON($task['id'], $vtodo, OC_Calendar_App::getTimezone());
	} catch(Exception $e) {
		OCP\Util::writeLog('tasks', $e->getMessage(), OCP\Util::ERROR);
	}
}
?>

<button id="new_task_button"><i class="icon-plus"></i> New task</button>

<form id="new_task" class="hidden">
	<input type="hidden" id="project_id" name="project_id" value="<?php p($project['id']); ?>" />
	<input type="hidden" id="calendar_id" name="calendar_id" value="<?php p($project['calendar_id']); ?>" />
	<p>
		<input type="text" id="new_summary" placeholder="Add a new task" name="summary" autocomplete="off" />
	</p>
	<p>
		<label for="new_assign">Assign</label>
		<input type="text" id="new_assign" placeholder="Unassigned" name="assigned" autocomplete="off" />
	</p>
	<p>
		<label for="new_due">Due</label>
		<input type="date" placeholder="No due date" id="new_due" name="duedate" />
	</p>
	<p>
		<label for="new_priority">Priority</label>
		<select id="new_priority" name="priority">
			<option value="">None</option>
			<option value="9">Low</option>
			<option value="5">Medium</option>
			<option value="1">High</option>
		</select>
	</p>
	<p>
		<textarea placeholder="Notes" name="description" id="new_notes"></textarea>
	</p>
	<p>
		<button name="add_task" id="new_add" ><i class='icon-plus'></i>Add this task</button> or <button id="cancel_new_task" class="tag"><i class="icon-remove"></i> Cancel task</button>
	</p>
</form>


<form id="edit_task" class="hidden">
	<input id="task_id" type="hidden" name="id" />
	<p>
		<label for="summary" class="hidden">Title</label>
		<input id="summary" name="summary" type="text" placeholder="Title" autocomplete="off" />
	</p>
	<p>
		<label for="due">Due</label>
		<input id="due" name="due" type="date" />
	</p>
	<p>
		<label for="assign">Assign</label>
		<input id="assign" name="assign" type="text" placeholder="Unassigned" autocomplete="off" />
	</p>
	<p>
		<label for="priority">Priority</label>
		<select id="priority" name="priority">
			<option value="">None</option>
			<option value="9">Low</option>
			<option value="5">Medium</option>
			<option value="1">High</option>
		</select>
	</p>
	<p>
		<label class="hidden" for="notes">Notes</label>
		<textarea id="notes" name="notes" placeholder="Notes"></textarea>
	</p>
	<p>
		<button id="update_task"><i class="icon-ok"></i> Save changes</button>
		<button id="cancel_task"><i class="icon-remove"></i> Cancel</button>
		<button id="delete_task"><i class="icon-trash"></i> Delete</button>
	</p>
</form>

<table id="tasks">
	<?php foreach($tasks as $task) { ?>
		<tr class="task" data-task_id="<?php p($task['id']); ?>">
			<td>
				<input data-task_id="<?php p($task['id']); ?>" class="complete_checkbox" type="checkbox" name="complete"<?php print_unescaped($task['complete'] == 100 ? " checked=\"checked\"" : "" ); ?> />
			</td>
			<td class="priority" data-priority="<?php p($task['priority']); ?>">
				<?php if ($task['priority'] > 6 && $task['priority'] <= 9) {
					p('!');
				} elseif ($task['priority'] > 3 && $task['priority'] < 7) {
					p('!!');
				} elseif ($task['priority'] >= 1 && $task['priority'] < 4) {
					p('!!!');
				} else {
					
				}?>
			</td>
			<td>
				<h2 class="task_summary"><?php p($task['summary']); ?></h2>
				<p class="task_description"><?php p($task['description']); ?></p>
			</td>
			<td class="due-date" data-due="<?php if($task['due']) p(date("Y-m-d", $task['due'])); ?>" data-assign="<?php p($task['assigned_to']); ?>">
				<?php if($task['due']) p( "Due " . date("D, M j", $task['due']) ); ?><br />
				<?php if($task['assigned_to']) p( "Assigned to " . $task['assigned_to']); ?>
			</td>
			<td class="completed-date">
				<?php if($task['completed']) p( "Completed " . date("D, M j", strtotime($task['completed'] )) ); ?><br />
				<?php if($task['completed_by']) p ( "By " . $task['completed_by'] ); ?>
			</td>
		</tr>
	<?php } ?>
</table>
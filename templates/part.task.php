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

// Select task
$query = OCP\DB::prepare( 'SELECT * FROM `*PREFIX*calendar_objects` WHERE `calendarid` = ? AND objecttype = "VTODO" AND id = ?' );
$result = $query->execute(array($calendar_id, $item));
$task = $result->fetchRow();

$object = OC_VObject::parse($task['calendardata']);
$vtodo = $object->VTODO;
try {
	$task = OC_Projects_App::arrayForJSON($task['id'], $vtodo, OC_Calendar_App::getTimezone());
} catch(Exception $e) {
	OCP\Util::writeLog('tasks', $e->getMessage(), OCP\Util::ERROR);
}

print_r($task);
/*
$meta = array();
if ($task['complete'] == 100) {
	if ($task['completed_by']) $meta[] = "Completed by $task[completed_by]";
	if ($task['completed']) $meta[] = date("D, M j", strtotime($task['completed'] ));
} elseif ($task['assigned_to'] || $task['due']) {
	if ($task['assigned_to']) $meta[] = $task['assigned_to'];
	if ($task['due']) $meta[] = date("D, M j", $task['due']);
}
?>
<? 
<li class="task<?php p( $task['completed'] ? ' complete' : '' ); ?>" data-task_id="<?php p($task['id']); ?>">
	<span class="task_priority" data-priority="<?php p($task['priority']); ?>"><?php p(OC_Projects_App::getPriority($task['priority'])); ?></span>
	<a href="<?php p( OCP\Util::linkTo( 'projects', 'index.php' ) . "/id/$project[id]/tasks/$task[id]" ); ?>">
		<h2 class="task_summary"><?php p($task['summary']); ?></h2>
		<p class="task_description"><?php p($task['description']); ?></p>
		<p class="task_meta<?php p($meta ? '' : ' hidden'); ?>"<?php 
			if ($task['assigned_to'])  print_unescaped(" data-assign='$task[assigned_to]'");
			if ($task['due'])          print_unescaped(" data-due='" . date("Y-m-d", $task['due']) . "'");
			if ($task['completed'])	   print_unescaped(" data-completed='$task[completed]'");
			if ($task['completed_by']) print_unescaped(" data-completed_by='$task[completed_by]'"); ?>><?php
				 p( join($meta, ' · ') ); 
		?></p>
	</a>
</li>

*/ ?>


<form id="edit_task"<?php if($task['complete'] == 100) p(" class='complete'"); ?>>
	<input id="task_id" type="hidden" name="id" value="<?php p($task['id']); ?>"/>
	<p>
		<label for="summary" class="hidden">Title</label>
		<input class="task_complete" type="checkbox" name="complete"<?php p($task['complete'] == 100 ? " checked" : "" ); ?>/>
		<input id="summary" name="summary" type="text" placeholder="Title" autocomplete="off" value="<?php p($task['summary']); ?>"/>
	</p>
	<p>
		<label for="due">Due</label>
		<input id="due" name="due" type="date" value="<?php if($task['due']) p(date("Y-m-d", $task['due'])); ?>" />
	</p>
	<p>
		<label>Completed</label>
		<span><?php if($task['completed']) p(date("D, M j", strtotime($task['completed'] ))); ?></span>
	</p>
	<p>
		<label for="assign">Assign</label>
		<input id="assign" name="assign" type="text" placeholder="Unassigned" autocomplete="off" value="<?php p($task['assigned_to']); ?>"/>
	</p>
	<p>
		<label>Completed by</label>
		<span><?php p($task['completed_by']); ?></span>
	</p>
	<p>
		<label for="priority">Priority</label>
		<select id="priority" name="priority">
			<option value="">None</option>
			<option value="9"<?php if ($task['priority'] > 6 && $task['priority'] <= 9 ) p(' selected'); ?>>Low</option>
			<option value="5"<?php if ($task['priority'] > 3 && $task['priority'] < 7 ) p(' selected'); ?>>Medium</option>
			<option value="1"<?php if ($task['priority'] >= 1 && $task['priority'] <= 4 ) p(' selected'); ?>>High</option>
		</select>
	</p>
	<p>
		<label class="hidden" for="notes">Notes</label>
		<textarea id="notes" name="notes" placeholder="Notes"><?php p($task['description']); ?></textarea>
	</p>
	<p>
		<button id="update_task"><i class="icon-ok"></i> Save changes</button>
		<button id="cancel_task"><i class="icon-remove"></i> Cancel</button>
		<button id="delete_task"><i class="icon-trash"></i> Delete</button>
	</p>
</form>
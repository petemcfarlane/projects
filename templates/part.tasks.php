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
	
	// Arrange tasks by $task['complete']
	if ($tasks) { 
		$sortArray = array();
		foreach($tasks as $task) {
			foreach($task as $key=>$value) {
				if(!isset($sortArray[$key])) {
					$sortArray[$key]=array();
				}
				$sortArray[$key][]=$value;
			}
		}
		array_multisort($sortArray['complete'], SORT_ASC, $tasks);
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

<ul id="tasks">
	<li class="task hidden" id="task_template">
		<input class="task_complete" type="checkbox" name="complete" />
		<span class="task_priority"></span>
		<h2 class="task_summary"></h2>
		<p class="task_description"></p>
		<p class="task_meta"></p>
	</li>

	<?php
	function getPriority($priority) {
		if ($priority > 6 && $priority <= 9) { // low
			p('!');
		} elseif ($priority > 3 && $priority < 7) { // medium
			p('!!');
		} elseif ($priority >= 1 && $priority < 4) { // high
			p('!!!');
		} 
	}
	
	foreach($tasks as $task) {
		$meta = array();
		if ($task['complete'] == 100) {
			if ($task['completed_by']) $meta[] = "Completed by $task[completed_by]";
			if ($task['completed']) $meta[] = date("D, M j", strtotime($task['completed'] ));
		} elseif ($task['assigned_to'] || $task['due']) {
			if ($task['assigned_to']) $meta[] = $task['assigned_to'];
			if ($task['due']) $meta[] = date("D, M j", $task['due']);
		}
		?>
		<li class="task<?php p( $task['completed'] ? ' complete' : '' ); ?>" data-task_id="<?php p($task['id']); ?>">
			<input class="task_complete" type="checkbox" name="complete"<?php p($task['complete'] == 100 ? " checked" : "" ); ?>/>
			<span class="task_priority" data-priority="<?php p($task['priority']); ?>"><?php p(getPriority($task['priority'])); ?></span>
			<h2 class="task_summary"><?php p($task['summary']); ?></h2>
			<p class="task_description"><?php p($task['description']); ?></p>
			<p class="task_meta<?php p($meta ? '' : ' hidden'); ?>"<?php 
				if ($task['assigned_to'])  print_unescaped(" data-assign='$task[assigned_to]'");
				if ($task['due'])          print_unescaped(" data-due='" . date("Y-m-d", $task['due']) . "'");
				if ($task['completed'])	   print_unescaped(" data-completed='$task[completed]'");
				if ($task['completed_by']) print_unescaped(" data-completed_by='$task[completed_by]'"); ?>>
					<?php p( join($meta, ' · ') ); ?>
			</p>
		</li>
	<?php }
	
	
	
	 /*foreach($tasks as $task) { ?>
		<li class="task<?php p( $task['completed'] ? ' complete' : '' ); ?>" data-task_id="<?php p($task['id']); ?>">
			
			<input data-task_id="<?php p($task['id']); ?>" class="complete_checkbox" type="checkbox" name="complete"<?php p($task['complete'] == 100 ? " checked" : "" ); ?> />
			
			<span class="priority" data-priority="<?php p($task['priority']); ?>"><?php ?></span>
			
			<h2 class="task_summary"><?php p($task['summary']); ?></h2>
			
			<p class="task_description"><?php p($task['description']); ?></p>

			<p class="task_meta assigned-due<?php p( ($task['assigned_to'] || $task['due']) ? '' : ' hidden' ); ?>">
				<span class="assigned_to" data-assign="<?php p($task['assigned_to']); ?>"><?php if($task['assigned_to']) p( $task['assigned_to']); ?></span>
				<?php if ($task['assigned_to'] && $task['due'] ) p(' · '); ?>
				<span class="due-date" data-due="<?php if($task['due']) p(date("Y-m-d", $task['due'])); ?>"><?php if($task['due']) p( " " . date("D, M j", $task['due']) ); ?></span>
			</p>
			<p class="task_meta complete-meta">
				<span class="completed_by"><?php if($task['completed_by']) p ( "Completed by " . $task['completed_by'] ); ?></span>
				<?php if ($task['completed_by'] && $task['completed'] ) p(' · '); ?>
				<span class="completed-date"><?php if($task['completed']) p( date("D, M j", strtotime($task['completed'] )) ); ?></span>
			</p>
		</li>
	<?php } */?>
</ul>
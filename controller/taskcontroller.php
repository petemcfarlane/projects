<?php

namespace OCA\Projects\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\Projects\Core\API;
use \OCA\AppFramework\Http\Request;
use \OCA\AppFramework\Http\RedirectResponse;

class TaskController extends Controller {
	
	public function __construct(API $api, Request $request, $projectController=null, $tasks=null) {
		parent::__construct($api, $request);
		$this->params = array('requesttoken' => \OC_Util::callRegister() );
		$this->renderas = isset($_SERVER['HTTP_X_PJAX']) ? '' : 'user';
		$projectController = $projectController===null ? new ProjectController($this->api, $this->request) : $projectController;
		$this->project = $projectController->getProject($this->request->id, $this->api->getUserId());
		if ($tasks) $this->tasks = $tasks;
	}
	
	public function redirectProjectsIndex() {
		$response = new RedirectResponse( $this->api->linkToRoute('projects.project.index') );
		$response->setStatus(303);
		return $response;
	}
	
	public function getTasks($calendarId) {
		
		if (isset($this->tasks)) return $this->tasks; // use mock get tests, for testing only

		// get calendar events of type VTODO for project calendar
		$calendar_tasks = \OC_Calendar_Object::all($calendarId);
		$user_timezone = \OC_Calendar_App::getTimezone();
		
		// for each todo, parse and return in array
		$tasks = array();
		foreach( $calendar_tasks as $task ) {
			if ( $task['objecttype'] !== 'VTODO' || is_null($task['summary']) ) continue;
			$object = \OC_VObject::parse($task['calendardata']);
			$vtodo = $object->VTODO;
			$task = array( 'id' => $task['id'] );
			$trans = array('\,' => ',', '\;' => ';');
			$task['summary'] = strtr($vtodo->getAsString('SUMMARY'), $trans);
			$task['description'] = strtr($vtodo->getAsString('DESCRIPTION'), $trans);
			$task['location'] = strtr($vtodo->getAsString('LOCATION'), $trans);
			$task['categories'] = $vtodo->getAsArray('CATEGORIES');
			$task['priority'] = $vtodo->getAsString('PRIORITY');
			$task['complete'] = $vtodo->getAsString('PERCENT-COMPLETE');
			if ( $due=$vtodo->DUE ) {
				$task['due_date_only'] = $due->getDateType() == \Sabre\VObject\Property\DateTime::DATE;
				$due = $due->getDateTime();
				$due->setTimezone(new \DateTimeZone($user_timezone));
				$task['due'] = $due->format('Y-m-d H:i:s');
			} else {
				$task['due'] = false;
			}
			if ( $completed=$vtodo->COMPLETED ) {
				$completed = $completed->getDateTime();
				$completed->setTimezone(new \DateTimeZone($user_timezone));
				$task['completed'] = $completed->format('Y-m-d H:i:s');
			}
			else {
				$task['completed'] = false;
			}
			array_push($tasks, $task);
		}
		return $tasks;
	}

	public function taskFromRequest($request=null) {
		if (isset($this->tasks)) return; // use mock get tests, for testing only
		$calendarId = $request->calendarId ? $request->calendarId : $this->createCalendar(); 
		$vtodo = new \OC_VObject('VTODO');
		$vtodo->setDateTime('CREATED', 'now', \Sabre\VObject\Property\DateTime::UTC);
		$vtodo->setUID();

		$vcalendar = new \OC_VObject('VCALENDAR');
		$vcalendar->add('PRODID', 'ownCloud Tasks');
		$vcalendar->add('VERSION', '2.0');
		$vcalendar->add($vtodo);

		$vtodo = $vcalendar->VTODO;
		$vtodo->setDateTime('LAST-MODIFIED', 'now', \Sabre\VObject\Property\DateTime::UTC);
		$vtodo->setDateTime('DTSTAMP', 'now', \Sabre\VObject\Property\DateTime::UTC);
		$vtodo->setString('SUMMARY', $request->task['summary']);
		$vtodo->setString('LOCATION', $request->task['location']);
		$vtodo->setString('DESCRIPTION', $request->task['description']);
		$vtodo->setString('PRIORITY', $request->task['priority']);
		// $vtodo->setString('CATEGORIES', $categories);

		if ( $due=$request->task['due'] ) {
			$timezone = \OC_Calendar_App::getTimezone();
			$timezone = new \DateTimeZone($timezone);
			$due = new \DateTime($due, $timezone);
			$vtodo->setDateTime('DUE', $due);
		} else {
			unset($vtodo->DUE);
		}

		$percent_complete = isset($request->task['percent_complete']) ? $request->task['percent_complete'] : null;
		$completed = isset($request->task['completed']) ? $request->task['completed'] : null;

		self::setComplete($vtodo, $percent_complete, $completed);
		\OC_Calendar_Object::add( $calendarId, $vcalendar->serialize());
	}

	public function createCalendar() {
		$calendarId = \OC_Calendar_Calendar::addCalendar($this->api->getUserId(), $this->project->getName(), 'VTODO');
		$projectController = new ProjectController($this->api, $this->request);
		$projectController->setCalendarId($this->project, $calendarId);
		return $calendarId;
	}

	public function updateTask($request=null) {
		if (isset($this->tasks)) return; // use mock get tests, for testing only

		$taskId = $request->taskId;
		$vcalendar = \OC_Calendar_App::getVCalendar($taskId);
		
		$vtodo = $vcalendar->VTODO;
		$vtodo->setString('SUMMARY', $request->task['summary']);
		$vtodo->setString('DESCRIPTION', $request->task['description']);
		$vtodo->setString('LOCATION', $request->task['location']);
		$vtodo->setString('PRIORITY', $request->task['priority']);
		// $vtodo->setString('CATEGORIES', $request->task['categories']);

		$due = $request->task['due'];
		if ($due) {
			$timezone = \OC_Calendar_App::getTimezone();
			$timezone = new \DateTimeZone($timezone);
			$due = new \DateTime($due, $timezone);
			$due->setTimezone($timezone);
			$vtodo->setDateTime('DUE', $due, \Sabre\VObject\Property\DateTime::LOCALTZ);
		}

		self::setComplete($vtodo, $request->task['complete'] ? '100' : '0');
		
		\OC_Calendar_Object::edit($taskId, $vcalendar->serialize());
	}


	public function deleteTask($request=null) {
		if (isset($this->tasks)) return; // use mock get tests, for testing only
		$id = $request->taskId;
		\OC_Calendar_App::getEventObject($id);
		\OC_Calendar_Object::delete($id);
	}

	
	public function setComplete($vtodo, $percent_complete, $completed=null) {
		if ($percent_complete) {
			$vtodo->setString('PERCENT-COMPLETE', $percent_complete);
		} else {
			$vtodo->__unset('PERCENT-COMPLETE');
		}
		
		if ($percent_complete === 100) {
			if (!$completed) $completed = 'now';
		} else {
			$completed = null;
		}
		
		if ($completed) {
			$timezone = \OC_Calendar_App::getTimezone();
			$timezone = new DateTimeZone($timezone);
			$completed = new DateTime($completed, $timezone);
			$vtodo->setDateTime('COMPLETED', $completed);
			\OCP\Util::emitHook('OC_Task', 'taskCompleted', $vtodo);
		} else {
			unset($vtodo->COMPLETED);
		}
	}


	/**
	 * @CSRFExemption
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function index() {
		if ( !$this->project || !$this->project->canRead() ) return $this->redirectProjectsIndex();
		$this->params = array_merge($this->params, (array)$this->project);
		$this->params['tasks'] = (array)$this->getTasks( $this->project->getCalendarId() );
		return $this->render('tasks/index', $this->params, $this->renderas, array('X-PJAX-URL'=>$this->api->linkToRoute('projects.task.index', array('id'=>$this->project->getId()))));
	}
	
	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function create() {
		if ( !$this->project || !$this->project->canCreate() ) return $this->redirectProjectsIndex();
		$this->taskFromRequest($this->request);
		$this->params = array_merge($this->params, (array)$this->project);
		$this->params['tasks'] = (array)$this->getTasks( $this->project->getCalendarId() );
		return $this->render('tasks/index', $this->params, $this->renderas, array('X-PJAX-URL'=>$this->api->linkToRoute('projects.task.index', array('id'=>$this->project->getId()))));
	}
	
	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function update() {
		if ( !$this->project || !$this->project->canUpdate() ) return $this->redirectProjectsIndex();
		$this->updateTask($this->request);
		$response = new RedirectResponse( $this->api->linkToRoute('projects.task.index', array('id'=>$this->project->getId())) );
		$response->setStatus(303);
		return $response;
	}
	
	/**
	 * @IsAdminExemption
	 * @IsSubAdminExemption
	 */
	public function destroy() {
		if ( !$this->project || !$this->project->canDelete() ) return $this->redirectProjectsIndex();
		$this->deleteTask($this->request);
		$response = new RedirectResponse( $this->api->linkToRoute('projects.task.index', array('id'=>$this->project->getId())) );
		$response->setStatus(303);
		return $response;
	}
}

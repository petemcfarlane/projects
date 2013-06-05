<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');
OCP\JSON::callCheck();

if ( isset ( $_POST['note_id'] ) ) 
	{
		// update note
		$note = OC_Projects_App::editNote($_POST['project_id'], $_POST['note']);
		// return note_id, 
		OCP\JSON::success(array('note' => $note));
		exit;
	} 

elseif ( isset ($_POST['trash_note_id'] ) ) 
	{
		// trash note
		$note = OC_Projects_App::trashNote($_POST['trash_note_id']);
		// return note_id, 
		OCP\JSON::success(array('note' => $note));
		exit;
	} 

elseif ( isset ($_POST['project_id'] ) ) 
	{
		// create new note
		$note = OC_Projects_App::newNote($_POST['project_id'], $_POST['note']);
		// return note_id 
		OCP\JSON::success(array('note' => $note));
		exit;
	}



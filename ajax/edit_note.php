<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');
OCP\JSON::callCheck();

if ( isset ( $_POST['edit_note_id'] ) ) 
 {
	// update note
	$note = OC_Projects_App::editNote($_POST['edit_note_id'], $_POST['note']);
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

elseif ( isset ($_POST['delete_note_permenantly'] ) ) 
 {
	// trash note
	$note = OC_Projects_App::deleteNotePermenantly($_POST['delete_note_permenantly']);
	// return note_id, 
	OCP\JSON::success(array('note' => $note));
	exit;
 } 

elseif ( isset ( $_POST['restore_note_id'] ) )
 {
 	// restore note from trah
 	$note = OC_Projects_App::restoreNote($_POST['restore_note_id']);
	// return note
	OCP\JSON::success(array('note' => $note));
 }

elseif ( isset ($_POST['project_id'] ) ) 
 {
	// create new note
	$note = OC_Projects_App::newNote($_POST['project_id'], $_POST['note']);
	// return note_id 
	OCP\JSON::success(array('note' => $note));
	exit;
 }



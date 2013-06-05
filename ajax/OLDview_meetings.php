<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');
OCP\JSON::callCheck();

if (isset($_POST['meeting_id'])) {

	$query 	  = OC_DB::prepare('SELECT * FROM *PREFIX*meetings WHERE id = ?');
	$result	  = $query->execute( array($_POST['meeting_id']) );
	$meeting = $result->fetchRow(); 

	print json_encode($meeting);

} elseif (isset($_POST['id'])) {

	$query 	  = OC_DB::prepare('SELECT id, date FROM *PREFIX*meetings WHERE project_id = ? ORDER BY date DESC');
	$result	  = $query->execute( array($_POST['id']) );
	$meetings = $result->fetchAll(); 

	foreach($meetings as $i => $meeting) {
		$meetings[$i]['date'] = date("jS M, Y", strtotime($meeting['date']));
	}

	print json_encode($meetings);

}?>
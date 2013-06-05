<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');
OCP\JSON::callCheck();

if ( isset($_POST['name']) && strlen($_POST['name']) > '1' ) {

	$query 	  = OC_DB::prepare('SELECT gid FROM *PREFIX*groups WHERE gid LIKE ? LIMIT 5');
	$result	  = $query->execute( array("%".$_POST['name']."%") );
	$groups   = $result->fetchAll(); 


	$query 	  = OC_DB::prepare('SELECT uid FROM *PREFIX*users WHERE uid LIKE ? LIMIT 5');
	$result	  = $query->execute( array("%".$_POST['name']."%") );
	$users    = $result->fetchAll(); 

	print json_encode( array( "groups" => $groups, "users" => $users ) );

}
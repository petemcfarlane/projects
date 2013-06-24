<?php 
// get creator
$creator = $project['creator'];

// list current users
$current_users = explode(',', $project['users'], -1);

// get all users (mark if current user or creator)
$uids = OC_User::getUsers();
$users = array();
	foreach($uids as $uid) {
		$users[$uid] = array("name" => OC_User::getDisplayName($uid) );
		if (in_array($uid, $current_users)) $users[$uid]['current_user'] = true;
		if ($uid == $creator) $users[$uid]['creator'] = true;
	}

// display list of users
?>
<ul id="people" data-project_id="<?php p($project['id']); ?>">
	<?php foreach ($users as $user => $u) { ?>
		<li class="<?php p (isset($u['current_user']) ? "current " : "" ); p(isset($u['creator']) ? "creator": ""); ?>" data-uid="<?php p($user); ?>">
			<img class="thumbnail_40" src="<?php p( OCP\Util::linkTo( 'user_photo', 'index.php' ) . "/photo/$user/40" ); ?>" />
			<?php p($u['name']); ?>
			<?php print_unescaped(isset($u['creator']) ? "<em style='font-weight:normal;'>(creator)</em>" : "" ); ?>
			<em class="hidden loading_person">loading...</em>
		</li>
	<?php } ?>
</ul>

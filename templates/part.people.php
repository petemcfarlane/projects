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
<h2 class="center">Select which users you want to collaberate with.</h2>
<p class="center">You can't remove the project creator.</p>

<ul id="people" data-project_id="<?php p($project['id']); ?>">
	<?php foreach ($users as $user => $u) { ?>
		<li<?php print ( isset($u['creator']) || isset($u['current_user']) ) ? " class='checked'": ""; ?> data-uid="<?php print $user; ?>"><label>
			<img class="thumbnail_60" src="<?php print( OCP\Util::linkTo( 'user_photo', 'index.php' ) . "/photo/$user/60" ); ?>" width="60" height="60" />
			<?php print $u['name']; ?>
			<input type="checkbox" name="users[]" value="<?php print $user; ?>" <?php print isset($u['current_user']) ? 'checked' : ''; ?><?php print isset($u['creator']) ? " disabled": ""; ?>/>
		</label></li>
	<?php } ?>
</ul>

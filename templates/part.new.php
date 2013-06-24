<?php 
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('projects');
OCP\JSON::callCheck();
?>
<div>
	<h2>Create a new project</h2>
	<form id="new_project_form" method="post">
		<input type="hidden" name="new_project" value="new" />
		<p>
			<label for="name" class="hidden">Name the project</label>
			<input id="name" name="name" type="text" placeholder="Name the project" autocomplete="off" />
		</p>
		<p>
			<label for="description" class="hidden">Add a description or extra details (optional)</label>
			<input id="description" name="description" type="text" placeholder="Add a description or extra details (optional)" autocomplete="off" />
		</p>
		<p>Select users to collaberate - you can change this later, too.</p>
	
		<?php /*<div class="invitees">			
			<div class="person invitee blank">
				<div class="icon"></div><input name="users[]" type="text" autocomplete="off" /><a class="remove"></a><ul class="suggestions"></ul>
			</div>
			<div class="person invitee blank">
				<div class="icon"></div><input name="users[]" type="text" autocomplete="off" /><a class="remove"></a><ul class="suggestions"></ul>
			</div>
			<div class="person invitee blank">
				<div class="icon"></div><input name="users[]" type="text" autocomplete="off" /><a class="remove"></a><ul class="suggestions"></ul>
			</div>
		</div><!-- end of .invitees --> */ 
		
		// get all users (mark if creator)
		$uids = OC_User::getUsers();
		$users = array();
			foreach($uids as $uid) {
				$users[$uid] = array("name" => OC_User::getDisplayName($uid) );
				if ( $uid == OC_User::getUser() ) $users[$uid]['creator'] = true;
			}
		?>
		
		<ul id="users">
			<?php foreach ($users as $user => $u) { ?>
				<li<?php print isset($u['creator']) ? " class='checked'": ""; ?>><label>
					<img class="thumbnail_60" src="<?php print( OCP\Util::linkTo( 'user_photo', 'index.php' ) . "/photo/$user/60" ); ?>" />
					<?php print $u['name']; ?>
					<input type="checkbox" name="users[]" value="<?php print $user; ?>" <?php print isset($u['creator']) ? "checked disabled": ""; ?>/>
					<?php //print_unescaped(isset($u['creator']) ? "<em style='font-weight:normal;'>(creator)</em>" : "" ); ?>
				</label></li>
			<?php } ?>
		</ul>


		<p>
			<input name="add_project" type="submit" value="Start project" />
		</p>
	</form>
</div>
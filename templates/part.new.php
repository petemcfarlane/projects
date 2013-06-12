<div>
	<h1>Start a project</h1>
	<p>The admin group is automatically invited to all projects.</p>
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
		<p>Add users or groups to collaberate - you can do this later, too.</p>
	
		<div class="invitees">			
			<div class="person invitee blank">
				<div class="icon"></div><input name="users[]" type="text" autocomplete="off" /><a class="remove"></a><ul class="suggestions"></ul>
			</div>
			<div class="person invitee blank">
				<div class="icon"></div><input name="users[]" type="text" autocomplete="off" /><a class="remove"></a><ul class="suggestions"></ul>
			</div>
			<div class="person invitee blank">
				<div class="icon"></div><input name="users[]" type="text" autocomplete="off" /><a class="remove"></a><ul class="suggestions"></ul>
			</div>
		</div><!-- end of .invitees -->
		<p>
			<input name="add_project" type="submit" value="Start project" />
		</p>
	</form>
</div>
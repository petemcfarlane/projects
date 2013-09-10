EDIT
<form method="post" action="{{ url('projects.project.update', {'id':id} ) }}">
	<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
	<label for="projectName">Enter a project name</label>
	<input type="text" name="projectName" id="projectName" value="{{ projectName }}" placeholder="Enter a project name" autocomplete="off" />
	<input type="submit" value="Update Project" />
</form>

<h1>New Project</h1>

<form action="{{ url('projects.project.create') }}" method="post">
	<label for="projectName">Enter a project name</label>
	<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
	<input type="text" name="projectName" id="projectName" placeholder="Enter a project name" autocomplete="off" />
	Who would you like to share this project with
	<input type="submit" value="Create Project" />
</form>
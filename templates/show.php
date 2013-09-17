<div class="row">
	<div class="columns small-12 center">
		<h1>Project {{ projectName }}</h1>
		<a href="{{ url('projects.project.index') }}" class="button menu-left">&lt; Projects</a>
	</div>
</div>
<div class="row">
	<div class="columns large-6">
		<form method="post" action="{{ url('projects.project.update', {'id':id} ) }}">
			<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
			<label>Project Name</label>
			<input type="text" name="projectName" value="{{ projectName }}" placeholder="Enter a project name" autocomplete="off" />
			<input type="submit" value="Save Name" class="button" />
		</form>
		<form method="post" action="{{ url('projects.project.destroy', {'id':id}) }}">
			<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
			<input type="submit" value="Delete Project" class="button" />
		</form>
	</div>
</div>

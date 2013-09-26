<div class="row">
	<div class="columns small-12 center">
		<h1>Project {{ name }}</h1>
		<a href="{{ url('projects.project.index') }}" class="button menu-left">&lt; Projects</a>
	</div>
</div>
<div class="row border-top">
	<div class="columns large-6">
		<form method="post" action="{{ url('projects.project.update', {'id':id} ) }}">
			<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
			<label>Project Name</label>
			<input type="text" name="name" value="{{ name }}" placeholder="Enter a project name" autocomplete="off" />
			<input type="submit" value="Save Name" class="button" />
		</form>
		<a href="{{ url('projects.detail.index', {'id':id}) }}">Details</a>
		<a href="{{ url('projects.notes.index', {'id':id}) }}">Notes</a>
		<a href="{{ url('projects.task.index', {'id':id}) }}">Tasks</a>
		<a class="share" data-item-type="projects" data-item="{{ id }}" data-possible-permissions="31" data-private-link="false" data-link"true">Share</a>
		<form method="post" action="{{ url('projects.project.destroy', {'id':id}) }}">
			<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
			<input type="submit" value="Delete Project" class="button" />
		</form>
	</div>
</div>

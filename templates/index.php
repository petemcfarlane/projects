<div class="row">
	<div class="columns large-12 center">
		<h1>Projects</h1>
		<a class="button menu-right" id="show-new-project-form">New</a>
	</div>
</div>
<div class="row border-top hide">
	<form action="{{ url('projects.project.create') }}" method="post" id="create-project">
		<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
		<div class="columns large-8">
			<input type="text" name="name" id="projectName" placeholder="Enter a project name" autocomplete="off" />
		</div>
		<div class="columns large-4">
			<a id="cancel-new-project" class="button right">Cancel</a>
			<input type="submit" value="Create Project" class="button right" />
		</div>
	</form>
</div>
{% if projects %}
	{% for project in projects %}
		<div class="row border-top hover-gray">
			<div class="columns large-12">
				<h2 class="project-name"><a href="{{ url('projects.project.show', {'id':project.id}) }}">{{ project.name }}</a></h2>
				<span class="project-links">
					<a href="{{ url('projects.detail.index', {'id':project.id}) }}">Details</a>
					<a href="{{ url('projects.notes.index', {'id':project.id}) }}">Notes</a>
					<a href="{{ url('projects.task.index', {'id':project.id}) }}">Tasks</a>
					<a class="share" data-item-type="projects" data-item="{{ project.id }}" data-possible-permissions="31" data-private-link="false" data-link"true">Share</a>
				</span>
			</div>
		</div>
				{# <h4>Show 12 completed tasks</h4>
					<li>Test This</li>
					<li>Build That</li>
					<li>Write Something</li>
					<li>Book Something</li>
					<li>Arrange Something</li>
					<li>Waiting for email from A</li>
					Add task
				</ul>
				<ul class="block" data-id="{{ project.id }}">
					<h4>Meetings</h4>
					<li>Phone call with Adam 1/9/2013</li>
					<li>Visit with Belle 30/08/2013</li>
					<li>Demo to Clive 25/08/2013</li>
					Add meeting
				</ul>
				<ul class="block" data-id="{{ project.id }}">
					<h4></h4>
					 <li>Platform: CS8670</li>
					<li>Budgeted? No</li>
					<li>Technical Requirements: asdfasdfasdfsadfsdfasdfasdffasdfasdfsadf</li>
					<a href="{{ url('projects.project.edit', {'id':project.id}) }}">Update details</a>
				</ul> #}
	{% endfor %}
{% endif %}

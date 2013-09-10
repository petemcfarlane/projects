<div class="row">
	<div class="columns small-6">
		<h1>Projects</h1>
	</div>
	<div class="columns small-6">
		<a class="button right" href="{{ url('projects.project.newForm') }}">Add Project</a>
	</div>
</div>
{% if projects %}
	{% for project in projects %}
		<div class="row border-top hover-gray">
			<div class="columns large-12">
				<h2 class="project-name"><a href="{{ url('projects.project.show', {'id':project.id}) }}">{{ project.projectName }}</a></h2>
				<span class="project-links">
					<a href="">Tasks</a>
					<a href="{{ url('projects.detail.index', {'id':project.id}) }}">Details</a>
					<a href="">Meetings</a>
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

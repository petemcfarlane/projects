<div class="row">
	<div class="columns large-12 center">
		<h1>Tasks for {{ name }}</h1>
		<a href="{{ url('projects.project.show', {'id':id}) }}" class="button menu-left">&lt; Project</a>
		<a class="button menu-right" id="show-new-task-form">New</a>
	</div>
</div>
<div class="row border-top hide">
	<form action="{{ url('projects.task.create', {'id':id}) }}" method="post" id="create-task">
		<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
		<input type="hidden" name="calendarId" value="{{ calendarId }}" />
		<div class="columns large-8">
			<input type="text" name="task[summary]" id="taskSummary" placeholder="Task summary" autocomplete="off" />
			<input type="text" name="task[description]" placeholder="Description" autocomplete="off" />
			<input type="text" name="task[location]" placeholder="Location" autocomplete="off" />
			<input type="number" name="task[priority]" min="1" max="9" />
			<input type="date" name="task[due]" />
		</div>
		<div class="columns large-4">
			<a id="cancel-new-task" class="button right">Cancel</a>
			<input type="submit" value="Add Task" class="button right" />
		</div>
	</form>
</div>
{% if tasks %}
	{% for task in tasks %}
		<div class="row border-top hover-gray">
			<div class="columns large-12">
				<div class="row">
					<div class="columns large-9">
						<input type="checkbox" class="task-complete" />
						<span class="task-priority">!!!</span>
						<h2 class="task-summary">{{ task.summary }}</h2>
					</div>
					<div class="columns large-3">
						{% if task.due %}
							<span class="task-due right">due {{ task.due|date("d/m/Y") }}</span>
						{% endif %}
					</div>
				</div>
				<div class="row">
					<div class="columns large-12">
						{% if task.description %}
							<p class="task-description">{{ task.description }}</p>
						{% endif %}
					</div>
	{#
					<form action="{{ url('projects.task.update', {'id':id, 'taskId':task.id}) }}" method="post">
						<input type="checkbox" name="task[complete]" {% if task.complete %}checked{% endif %} />
						<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
						<input type="text" name="task[summary]" value="{{ task.summary }}" value="{{ task.summary }}" id="taskSummary" placeholder="Task summary" autocomplete="off" />
						<input type="text" name="task[description]" value="{{ task.description }}" placeholder="Description" autocomplete="off" />
						<input type="text" name="task[location]" value="{{ task.location }}" placeholder="Location" autocomplete="off" />
						<input type="number" name="task[priority]" value="{{ task.priority }}" min="1" max="9" />
						<input type="date" name="task[due]" value="{{ task.due }}" />
						<input type="submit" class="button" value="Update" />
					</form>
					<form action="{{ url('projects.task.destroy', {'id':id, 'taskId':task.id}) }}" method="post">
						<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
						<input type="submit" class="button" value="Delete" />
					</form>
	#}
				</div>
			</div>
		</div>
		<!-- {% if task.categories %} -->
			<!-- {% for category in task.categories %} -->
				<!-- {{ category }} -->
			<!-- {% endfor %} -->
		<!-- {% endif %} -->
		{# task.complete #}
		{# task.completed #}
	{% endfor %}
{% endif %}
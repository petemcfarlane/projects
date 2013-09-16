<div class="row">
	<div class="columns small-12">
		<h1>Notes for {{ projectName }}</h1>
	</div>
</div>
{% if notes %}
	{% set i = 1 %}
	<div class="row">
		{% for note in notes %}
			<div class="columns large-4">
				<article class="note paper">
					<a href="{{ url('projects.notes.show', {'id':id, 'noteId':note.id}) }}">
						{{ note.note }}
					</a>
				</article>
			</div>
			{% if i % 3 == 0 %}</div><div class="row">{% endif %}
			{% set i=i+1 %}
		{% endfor %}
	</div>
{% endif %}

<form method="post" action="{{ url('projects.notes.create', {'id':id	}) }}">
	<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
	<textarea name="note"></textarea>
	<input type="submit" value="Save" />
</form>

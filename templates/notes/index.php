<div class="row">
	<div class="columns large-12 center">
		<h1>Notes for {{ name }}</h1>
		<a href="{{ url('projects.project.show', {'id':id}) }}" class="button menu-left">&lt; Project</a>
		<a href="{{ url('projects.notes.newNote', {'id':id}) }}" class="button menu-right">New</a>
	</div>
</div>
{% if notes %}
	{% set i = 1 %}
	{% set totalNotes = notes|length %}
	<div class="row">
		{% for note in notes %}
			<div class="columns large-4">
				<a href="{{ url('projects.notes.show', {'id':id, 'noteId':note.id}) }}" class="no-underline">
					<article class="note paper fade-bottom height-240">
						{{ note.note|raw }}
					</article>
				</a>
			</div>
			{% if i % 3 == 0 and i != totalNotes %}</div><div class="row">{% endif %}
			{% set i=i+1 %}
		{% endfor %}
	</div>
{% endif %}

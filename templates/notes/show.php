<div class="row">
	<div class="columns large-12 center">
		<h1>Edit Note</h1>
		<a href="{{ url('projects.notes.index', {'id':id}) }}" class="button menu-left">&lt; Notes</a>
	</div>
</div>
<div class="row">
	<div class="columns large-12">
		<form method="post" id="edit-note" action="{{ url( 'projects.notes.update', {'id':id, 'noteId':note.id}) }}">
			<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
			<input type="hidden" name="note" id="note-input" value="{{ note.note }}" />
				<div class="paper">
					<article contenteditable="true" tabindex="0" id="note-content">{{ note.note|raw }}</article>
				</div>
			<span class="note-status">All saved</span>
			<input type="submit" value="Save" class="button hide" id="save-note"/>
		</form>
		<form method="post" action="{{ url('projects.notes.destroy', {'id':id, 'noteId':note.id}) }}">
			<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
			<input type="submit" name="submit" value="Delete" class="button" />
		</form>
	</div>
</div>

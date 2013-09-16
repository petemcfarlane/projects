<div class="row">
	<div class="columns large-12">
		<a href="{{ url('projects.notes.index', {'id':id}) }}">Back to all project notes</a>
	</div>
</div>
<div class="row">
	<div class="columns large-12">
		<div class="paper">
			<form method="post" action="{{ url( 'projects.notes.update', {'id':id, 'noteId':note.id}) }}">
				<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
				<textarea name="note">{{ note.note }}</textarea>
				<input type="submit" name="submit" value="Update" />
			</form>
		</div>
	</div>
</div>
<form method="post" action="{{ url('projects.notes.destroy', {'id':id, 'noteId':note.id}) }}">
	<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
	<input type="submit" name="submit" value="Delete" />
</form>

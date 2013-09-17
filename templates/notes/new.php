<div class="row">
	<div class="columns large-12 center">
		<h1>New Note</h1>
		<a href="{{ url('projects.notes.index', {'id':id}) }}" class="button menu-left">&lt; Cancel</a>
	</div>
</div>
<div class="row">
	<div class="columns large-12">
		<form method="post" id="new-note" action="{{ url( 'projects.notes.create', {'id':id}) }}">
			<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
			<input type="hidden" name="note" id="note-input" value="" />
				<div class="paper">
					<article contenteditable="true" tabindex="0" id="note-content"></article>
				</div>
			<input type="submit" value="Save" id="save-note" class="hide button"/>
		</form>
	</div>
</div>

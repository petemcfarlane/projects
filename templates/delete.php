DELETE
Project Name: {{ projectName }}
<form method="post" action="{{ url('projects.project.destroy', {'id':id}) }}">
	<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
	<input type="submit" value="Confirm delete" />
</form>
<a href="{{ url('projects.project.show', {'id':id}) }}">cancel</a>

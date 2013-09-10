Detail DELETE
Project Name: {{ projectName }}
<form method="post" action="{{ url('projects.detail.destroy', {'id':id, 'detailKey':detail.detailKey}) }}">
	<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
	<input type="submit" value="Confirm delete" />
</form>
<a href="{{ url('projects.detail.show', {'id':id, 'detailKey':detail.detailKey}) }}">cancel</a>

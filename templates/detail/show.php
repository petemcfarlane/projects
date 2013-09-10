SHOW Detail 
{{ detail.detailKey }} : {{ detail.detailValue }}

<form method="post" action="{{ url( 'projects.detail.update', {'id':id, 'detailKey':detail.detailKey}) }}">
	<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
	{{ detail.detailKey }}
	<input type="text" name="detailValue" value="{{ detail.detailValue }}" placeholder="Enter a value" />
	<input type="submit" value="Update" />
</form>
<a href="{{ url( 'projects.detail.delete', {'id':id, 'detailKey':detail.detailKey}) }}" class="button">Delete</a>

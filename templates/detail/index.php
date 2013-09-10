<div class="row">
	<div class="columns small-12">
		<h1>{{ projectName }} Details</h1>
	</div>
</div>
{% if details %}
	{% for detail in details %}
		<div class="row detail border-bottom">
			<form method="post" action="{{ url('projects.detail.update', {'id':id, 'detailKey':detail.detailKey}) }}">
				<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
				<div class="columns large-2 detail-key large-align-right">{{ detail.detailKey }}<input type="hidden" name="detailKey" class="detail-key" value="{{ detail.detailKey }}"/></div>
				<div class="columns large-10 detail-value">
					<div class="row">
						<div class="columns large-9"><textarea name="detailValue" class="illusion">{{ detail.detailValue }}</textarea></div>
						<div class="columns large-3 align-right">
							<input type="submit" value="Update" class="save-field" />
							</form>
							<form class="inline" method="post" action="{{ url('projects.detail.destroy', {'id':id, 'detailKey':detail.detailKey}) }}">
								<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
								<button type="submit" title="Delete" class="btn-action"><img class="icon-delete" src="/core/img/actions/delete.svg" /></button>
							</form>
						</div>
					</div>
				</div>
		</div>
	{% endfor %}
{% endif %}
<div class="row">
	<div class="columns large-10 right">
		<select id="select-add-field" class="margin-top-bottom">
			<option>Add field</option>
			<option value="Project type">Project type</option>
			<option value="Platform">Platform</option>
			<option value="Technical authority">Technical authority</option>
			<option value="Commercial authority">Commercial authority</option>
			<option value="Technical requirements">Technical requirements</option>
			<option value="Commercial requirements">Commercial requirements</option>
			<option value="Other requirements">Other requirements</option>
			<option value="Purchace decision">Purchace decision</option>
			<option value="Supply evaluation">Supply evaluation</option>
			<option value="Optimize by">Optimize by</option>
			<option value="Manufacture date">Manufacture date</option>
			<option value="Retail date">Retail date</option>
			<option value="Minimum order">Minimum order</option>
			<option value="Ramp year">Ramp year 1</option>
			<option value="Ramp year">Ramp year 2</option>
			<option value="Ramp year">Ramp year 3</option>
			<option value="Territories">Territories</option>
			<option value="Retailers">Retailers</option>
			<option value="BOM">BOM</option>
			<option value="RRP">RRP</option>
			<option value="License fee">License fee</option>
			<option value="Budgeted">Budgeted</option>
			<option value="OEM">OEM</option>
			<option value="Convince">Convince</option>
			<option value="General notes">General notes</option>
			<option value="Risk assessment">Risk assessment</option>
			<option value="Other...">Other...</option>
		</select>
	</div>
</div>
<div class="row">
	<form method="post" action="{{ url( 'projects.detail.create', {'id':id} ) }}">
		<input type="hidden" name="requesttoken" value="{{ requesttoken }}" />
		<div class="columns large-2 large-align-right">
			<input type="text" name="detailKey" id="new-detail-key" placeholder="New detail..." class="gray-border-box" autocomplete="off" />
			<span id="detail-key-holder" class="detail-key"></span>
		</div>
		<div class="columns large-10">
			<div class="row">
				<div class="columns large-9"><textarea name="detailValue" id="new-detail-value" class="gray-border-box" ></textarea></div>
				<div class="columns large-3 align-right">
				<input type="submit" value="Add detail" id="add-detail-submit" />
			</div>
		</div>
	</form>
</div>

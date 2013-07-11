<?php 
$query 	 = OC_DB::prepare('SELECT * FROM *PREFIX*projects_meta WHERE project_id = ?');
$result	 = $query->execute( array( $project['id'] ) );
$details = $result->fetchAll();
$preset_details = array("Type", "Platform", "Technical Authority", "Commercial Authority", "Minimum Quantity", "Ramp-up 1st year", "Ramp-up 2nd year", "Ramp-up 3rd year", "Territories", "Retailers", "BOM", "RRP", "License Fee", "Budget", 
		"ODM / OEM" );
?>
<form id="edit_details">

	<input id="project_id" name="project_id" value="<?php print $project['id']; ?>" type="hidden" />

	<p>
		<label id="project_name_label" for="project_name" class="hidden">Project Name</label>
		<input id="project_name" name="name" type="text" placeholder="Project Name" value="<?php print $project['name'];?>" />
		<label id="project_description_label" for="project_description" class="hidden">Project Description</label>
		<input id="project_description" name="description" type="text" placeholder="Add a project description" value="<?php print $project['description']; ?>" />
	</p>
	
	
	<?php
	foreach ($details as $detail) { ?>
		<p>
			<label for="in_<?php p($detail['meta_key']); ?>"><?php p($detail['meta_key']); ?></label>
			<input id="in_<?php p($detail['meta_key']); ?>" name="<?php p($detail['meta_key']); ?>" value="<?php p($detail['meta_value']); ?>" type="text" />
		</p>
	<?php 
	$pos = array_search($detail['meta_key'], $preset_details);
	unset( $preset_details[$pos] ); 
	}
	 
	 
	 /*<p>
		<label for="type">Type:</label>
		<input id="type" name="project_type" value="<?php print $project['project_type']; ?>" type="text" />
	</p>
	<p>
		<label for="platform">Platform:</label>
		<input id="platform" name="platform" value="<?php print $project['platform']; ?>" type="text" />
	</p>
	<p>
		<label for="technical_authority">Technical Authority:</label>
		<input id="technical_authority" name="technical_authority" value="<?php print $project['technical_authority']; ?>" type="text" />
	</p>
	<p>
		<label for="commercial_authority">Commercial Authority:</label>
		<input id="commercial_authority" name="commercial_authority" value="<?php print $project['commercial_authority']; ?>" type="text" />
	</p>
	<p>
		<label for="minimum">Minimum Quantity:</label>
		<input id="minimum" name="minimum" type="number" value="<?php print $project['minimum']; ?>" />
	</p>
	<p>
		<label for="ramp1">Ramp-up 1st year:</label>
		<input id="ramp1" name="ramp1" type="number" value="<?php print $project['ramp1']; ?>" />
	</p>
	<p>
		<label for="ramp2">Ramp-up 2nd year:</label>
		<input id="ramp2" name="ramp2" type="number" value="<?php print $project['ramp2']; ?>" />
	</p>
	<p>
		<label for="ramp3">Ramp-up 3rd year:</label>
		<input id="ramp3" name="ramp3" type="number" value="<?php print $project['ramp3']; ?>" />
	</p>
	<p>
		<label for="territories">Territories:</label>
		<input id="territories" name="territories" type="text" value="<?php print $project['territories']; ?>" />
	</p>
	<p>
		<label for="retailers">Retailers:</label>
		<input id="retailers" name="retailers" type="text" value="<?php print $project['retailers']; ?>" />
	</p>
	<p>
		<label for="bom">BOM:</label>
		<input id="bom" name="bom" type="text" value="<?php print $project['bom']; ?>" />
	</p>
	<p>
		<label for="rrp">RRP:</label>
		<input id="rrp" name="rrp" type="text" value="<?php print $project['rrp']; ?>" />
	</p>
	<p>
		<label for="license_fee">License fee / royalty:</label>
		<input id="license_fee" name="license_fee" type="text" value="<?php print $project['license_fee']; ?>" />
	</p>
	<p>
		<label for="budgeted">Budgeted:</label>
		<select id="budgeted" name="budgeted">
			<option value="0">no</option>
			<option value="1"<?php print ($project['budgeted']==1) ? "selected='selected'" : ''; ?>>yes</option>
		</select>
	</p>
	<p>
		<label for="odm_oem">Preferred ODM / OEM:</label>
		<input id="odm_oem" name="odm_oem" type="text" value="<?php print $project['odm_oem']; ?>" />
	</p> */?>
	<p>
		<label for="new_detail">Add another detail:</label>
		<select id="new_detail">
			<option></option>
			<?php foreach ($preset_details as $key) { ?>
				<option><?php p($key); ?></option>
			<?php } ?>
			<option value="other">Other...</option>
		</select>
	</p>

</form>
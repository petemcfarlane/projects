App for managing projects, on owncloud.

Cloud users will be able to create projects, edit projects and share projects

Projects are made up of the following components

	Details

	Tasks

	Notes

	Contacts

	Files

	Emails

There will also be a page for people to share projects, and a page to view the project history.

DATABASE DESIGNS

	projects
		*id
		*name(150)
		description(250)
		*creator
		*users
		calendar_id
		root_dir_id
		status
		
	projects_meta
		#id
		project_id
		meta_key (
			project_type
			platform
			technical_authority
			commercial_authority
			technical_aims
			present_quotation
			negotiate_order
			requirements
			commercial_aims
			purchace_decision
			evaluation_sent
			optimisation_done
			manufacture
			release_date
			minimum
			ramp1
			ramp2
			ramp3
			territories
			retailers
			bom
			rrp
			license_fee
			budgeted
			odm_oem
		)
		meta_value

	projects_actions
		#id
		project_id
		uid
		uaction
		target_type
		target_id
		atime
		excerpt

	projects_notes
		#id
		project_id
		parent_id
		creator
		status
		atime
		note
		
	projects_history
		id
		project_id
		uid
		table-column
		value
		undo_id
		redo_id
		created_at

projects 
		GET - list projects
		POST - create project
		
projects/id/123
				GET - show project home page
				
projects/id/123/archive
						GET - N/A
						POST - archive project
						DELETE - 

projects/id/12/tasks
					POST - create	
projects_id/12/tasks/123
						POST - update
						DELETE - delete
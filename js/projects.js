$(document).ready(function () {
	
	/* ------------------------
	 * PJAX
	 * -----------------------*/
	
	if ($.support.pjax) {
		$(document).pjax('#content a', '#content'); // every 'a' within '#content' to be clicked will load the content in '#content'
	}
	
	/* ------------------------
	 * Functions
	 * -----------------------*/
	
	function formatDate(date) {
		
		var weekday = new Array(7);
			weekday[0]="Sun";
			weekday[1]="Mon";
			weekday[2]="Tue";
			weekday[3]="Wed";
			weekday[4]="Thu";
			weekday[5]="Fri";
			weekday[6]="Sat";
		
		var month = new Array(12);
			month[0]="Jan";
			month[1]="Feb";
			month[2]="Mar";
			month[3]="Apr";
			month[4]="May";
			month[5]="Jun";
			month[6]="Jul";
			month[7]="Aug";
			month[8]="Sep";
			month[9]="Oct";
			month[10]="Nov";
			month[11]="Dec";
			
			today = new Date();
			today.setHours(00,00,00);
			fortnight = new Date(today);
			fortnight.setDate(fortnight.getDate()+14);
			if ($.type(date)=="string") { // ie, if in mysql date-time format, yyyy-mm-dd hh:ii:ss, convert to js date
				t = date.split(/[- :]/);
				date = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
			} else { // else hopefully it's a unix timestamp. damn you javascript, damn you
				date = new Date(date);
			}
			if ((date > today) && (date < fortnight)) {
				return(weekday[date.getDay()] + ", " + month[date.getMonth()] + " " + date.getDate() );
			} else {
				return(date.getDate() + "/" + (date.getMonth()+1) + "/" + date.getFullYear() );
			}
	}
	
	/* ------------------------
	 * Projects
	 * -----------------------*/
	
	// New project button clicked
	$('#content').on('click', '#new_project_button', function() {
		$.get(OC.filePath('projects', 'templates', 'part.new.php'), function(data) {
			$('#main').html(data);
			$('#new_project_form #name').focus();
		});
	});
	// select users
	$('#content').on('change', '#users li input[type="checkbox"]', function() {
		if ($(this).prop('checked')) {
			$(this).parent().parent().addClass('checked');
		} else {
			$(this).parent().parent().removeClass('checked');
		}
	})
	
	// Submit new project
	$('#content').on('submit', '#new_project_form', function(e) {
		e.preventDefault();
		$.post( OC.filePath('projects', 'ajax', 'edit_project.php'), $(this).serialize(), function(data) {
			var url = OC.filePath('projects', null, 'index.php')+'/id/'+data.project_id;
			$.pjax({url: url, container: '#content'});
		}, "json");
	});
	
	// Archive project
	$('#content').on('click', '#archive_project', function(e) {
		e.preventDefault();
		if (confirm('Do you really want to archive this project?')) {
			$.post( OC.filePath('projects', 'ajax', 'edit_project.php'), 'archive_project='+$(this).attr('data-project_id'), function(data) {
				var url = OC.filePath('projects', null, 'index.php');
				$.pjax({url: url, container: '#content'});
			}, "json");
		}
	});
	
	// Show archived projects 
	$('#content').on('click', '#show_archived_projects', function() {
		if ( $('#archived_project_list').hasClass('hidden') ) {
			$('#archived_project_list').removeClass('hidden');
			$('#show_archived_projects').html('<i class="icon-trash"></i> Hide Trash');
		} else {
			$('#archived_project_list').addClass('hidden');
			$('#show_archived_projects').html('<i class="icon-trash"></i> Show Trash');
		}
	});
	
	// Restore project
	$('#content').on('click', '.restore_archived_project', function() {
		$.post( OC.filePath('projects', 'ajax', 'edit_project.php'), 'restore_archived_project='+$(this).attr('data-project_id'), function(data) {
			console.log(data);
			var url = OC.filePath('projects', null, 'index.php');
			$.pjax({url: url, container: '#content'});
		}, "json");
	});
	
	/* ------------------------
	 * Details
	 * -----------------------*/
	
	// Add a new detail
	$('#content').on('change', '#new_detail', function() {
		var key = $(this).val(), input = document.createElement("input"), label = document.createElement("label"), p = document.createElement("p");
		if (key == "other") {
			var other = prompt("Please enter the detail label, e.g. \"Location\"");
			document.getElementById('new_detail').selectedIndex = -1;
			if (other == null) return
			input.type = "text"; input.id = "in_"+other; input.name = other;
			label.htmlFor = "in_"+other; label.innerHTML = other;
		} else {
			input.type = "text"; input.id = "in_"+key; input.name = key;
			label.htmlFor = "in_"+key; label.innerHTML = key;
			$('#new_detail option:selected').remove();
		}
		p.appendChild(label);
		p.appendChild(input);
		$(this).parent().before(p);
		input.focus();
	});


	// Ajax edit details
	$('#content').on("change", "#edit_details input[type='text']", function() {
		var input = $(this)

		$.ajax({
			url: OC.filePath('projects', 'ajax', 'edit_details.php'), 
			data: input.serialize()+'&project_id='+($('#project_id').val()),
			type: "POST",
			dataType: 'json',
			beforeSend: function() {
				input.addClass('loading_details');
			},
			success: function(data) {
				console.log(data);
				input.removeClass('loading_details');
			}
		});

	});

	/* ------------------------
	 * Tasks
	 * -----------------------*/
	
	// Ajax add task
	$('#content').on('click', '#new_task_button', function() {
		$('#new_task input[type="text"], #new_task input[type="email"], #new_task textarea').val('');
		$('#new_task').draggable().fadeIn("fast");
		$('#new_task #new_summary').focus();
	});
		$('#content').on("submit", '#new_task', function(e) {
			e.preventDefault();
			$.post( OC.filePath('projects', 'ajax', 'edit_task.php'), $(this).serialize(), function(data) {
				$('#calendar_id').val(data['calendar_id']);
				NewTask = $('#task_template').clone().attr('data-task_id', data.task.id).removeClass('hidden').removeAttr('id');
				$('#tasks').prepend(NewTask);
				populateTask(data.task);
				$('#new_task').hide();
			}, "json");
		});
		$('#content').on('click', '#cancel_new_task', function(e) {
			e.preventDefault();
			$('#new_task').fadeOut("fast");
		});
	
	// Ajax complete task
	$('#content').on("change", "#tasks .task_complete", function() {
		var checked = $(this).is(':checked') ? 100 : 0;
		$.post( OC.filePath('projects', 'ajax', 'edit_task.php'), "id="+$(this).parent().attr("data-task_id")+"&type=complete&checked="+checked+"&project_id="+$('#project_id').val(), function(data) {
			populateTask(data.task);
		}, "json");
	});

	// Ajax edit task
/*	var task = new Array();
	$("#content").on("click", "priority, .task_summary, .task_description, .task_meta", function() {
		var t = $(this).parent();
		$('#edit_task #task_id').val( t.attr('data-task_id') );
		$('#edit_task #summary').val( t.find('.task_summary').text() );
		$('#edit_task #due').val( t.find('.task_meta').attr('data-due') ); 
		$('#edit_task #assign').val( t.find('.task_meta').attr('data-assign') );
		$('#edit_task #priority').val( t.find('.task_priority').attr('data-priority') );
		$('#edit_task #notes').val( t.find('.task_description').text() );
		$('#edit_task').draggable().fadeIn("fast");
	});
		// save and update, on update
		$('#content').on('click', '#update_task', function(e){
			e.preventDefault();
			$.post( OC.filePath('projects', 'ajax', 'edit_task.php'), $('#edit_task').serialize() + "&type=update", function(data) {
				populateTask(data.task);
			}, "json");
			$('#edit_task').fadeOut("fast");
		});
		// close, on cancel
		$('#content').on('click', '#cancel_task', function(e){
			e.preventDefault();
			$('#edit_task').fadeOut("fast");
		});
		// delete the task
		$('#content').on('click', '#delete_task', function(e){
			e.preventDefault();
			//var r = confirm("Are you sure you want to delete this task? This operation cannot be undone.");
			//if (r == true) {
				$.post( OC.filePath('projects', 'ajax', 'edit_task.php'), "id="+ task['id'] + "&type=delete", function(data) {
					$("#tasks").find("[data-task_id='" + data.data['id'] + "']").remove();
				}, "json");
			//}
			$('#edit_task').fadeOut("fast");
		});
*/
		
	// populate the task from json data
	function populateTask(data) {
		var task = $("#tasks").find("[data-task_id='" + data['id'] + "']");
		
		var priority = parseInt(data.priority);
		if ( priority < 4 ) { 
			task.find(".task_priority").text('!!!').attr('data-priority', priority);
		} else if( priority > 3 && priority < 7 ) {
			task.find(".task_priority").text('!!').attr('data-priority', priority);
		} else if ( priority > 7 ) {
			task.find(".task_priority").text('!').attr('data-priority', priority);
		} else {
			task.find(".task_priority").text('').attr('data-priority', '');
		}
		
		task.find(".task_summary").text(data.summary);
		
		task.find('.task_description').text(data.description);

		var due = new Date(parseInt(data.due)*1000);
		data.assigned_to  ? task.find('.task_meta').attr("data-assign", data.assigned_to) : task.find('.task_meta').removeAttr('data-assign');
		data.due 		  ? task.find('.task_meta').attr("data-due", due.getFullYear() + "-" + ("0" + (due.getMonth()+1)).slice(-2) + "-" + ("0" + due.getDate()).slice(-2)) : task.find('.task_meta').removeAttr('data-due');
		data.completed_by ? task.find('.task_meta').attr("data-completed_by", data.completed_by) : task.find('.task_meta').removeAttr('data-completed_by');
		data.completed    ? task.find('.task_meta').attr("data-completed", data.completed) : task.find('.task_meta').removeAttr('data-completed');

		var meta = [];

		task.removeClass('complete');
		if (data.complete == 100) {
			task.addClass('complete');
			if (data.completed_by) meta.push("Completed by " + data.completed_by );
			if (data.completed)    meta.push(formatDate(data.completed));
		} else if (data.assigned_to || data.due) {
			if (data.assigned_to)  meta.push(data.assigned_to);
			if (data.due)          meta.push(formatDate(parseInt(data.due)*1000));
		}
		
		if (meta.length > 0) {
			task.find('.task_meta').removeClass('hidden').text(meta.join(' · '));
		} else {
			task.find('.task_meta').addClass('hidden');
		}
	}

	/* ------------------------
	 * Notes
	 * -----------------------*/
	
	// on add new note
	$('#content').on('click', '#new_note_button', function() {
		$('#new_note').fadeIn("fast").draggable();
		$('#note').focus();
	});
	$('#content').on('submit', '#new_note', function(e) {
		e.preventDefault();
		$.post( OC.filePath('projects', 'ajax', 'edit_note.php'), $(this).serialize(), function(data) {
			var article = $('#note_template').clone();
			article.attr('id', 'note_id_'+data.note.id );
			article.find('.content').html(data.note.note);
			article.find('footer span').html('Created by ' + data.note.creator + ' on ' + formatDate(data.note.atime) )
			article.find('.edit_button, .trash_button').attr('data-note_id', data.note.id );
			article.insertAfter('#note_template');
			$('#new_note').fadeOut("fast");
			$('#note').val("");
		}, 'json');
	});
	$('#content').on('click', '#cancel_new_note', function() {
		$('#new_note').fadeOut("fast");
	});
	
	// edit note
	$('#content').on('click', '.note .edit_button', function() {
		var id = $(this).attr('data-note_id'), content = $('#note_id_'+id).find('.content');
		content.hide().after('<textarea class="note_'+id+'">'+content.html()+'</textarea>');
		$('.note_'+id).select();
		$('#note_id_'+id).find('.meta, .edit_button, .trash_button').hide()
		$('#note_id_'+id).find('footer').append('<button class="submit_changes">Submit Changes</button><button class="cancel_changes">Cancel Changes</button>');
		$('.submit_changes').on('click', function() {
			$.post( OC.filePath('projects', 'ajax', 'edit_note.php'), "edit_note_id="+id+"&note="+$('.note_'+id).val(), function(data) {
				var article = $('#note_id_'+id);
				article.attr('id', 'note_id_'+data.note.id );
				article.find('.content').html(data.note.note);
				article.find('footer span').html('Updated by ' + data.note.creator + ' on ' + formatDate(data.note.atime) )
				article.find('.edit_button, .trash_button').attr('data-note_id', data.note.id );
				article.find('.content, .meta, .edit_button, .trash_button').show()
				article.find('textarea, .submit_changes, .cancel_changes').remove()
			}, 'json');
		});
		$('.cancel_changes').on('click', function() {
			$('#note_id_'+id).find('.content, .meta, .edit_button, .trash_button').show()
			$('#note_id_'+id).find('.submit_changes, .cancel_changes, .note_'+id).remove()
		});
	});
	
	// delete note 
	$('#content').on('click', '.note .trash_button', function() {
		var id = $(this).attr('data-note_id');
		$.post( OC.filePath('projects', 'ajax', 'edit_note.php'), "trash_note_id="+id, function(data) {
			var article = $('#trash_template').clone();
			article.attr('id', 'note_id_'+data.note.id );
			article.find('.content').html(data.note.note);
			article.find('footer span').html('Trashed by ' + data.note.creator + ' on ' + formatDate(data.note.atime) )
			article.find('.restore, .delete_permenantly').attr('data-note_id', data.note.id );
			article.insertAfter('#trash_template');
			$('#note_id_'+id).fadeOut("fast").remove();
		}, 'json');
	});
	
	// show trashed notes
	$('#content').on('click', '#show_trash_notes', function() {
		if ($('#trash_notes_list').hasClass('hidden')) {
			$('#trash_notes_list').removeClass('hidden');
			$('#show_trash_notes').html('<i class="icon-trash"></i> Hide Trash');
		} else {
			$('#trash_notes_list').addClass('hidden');
			$('#show_trash_notes').html('<i class="icon-trash"></i> Show Trash');
		}
	});
	
	// restore note
	$('#content').on('click', '.note .restore', function() {
		var id = $(this).attr('data-note_id');
		$.post( OC.filePath('projects', 'ajax', 'edit_note.php'), "restore_note_id="+id, function(data) {
			$('#note_id_'+data.note.id).remove();
			var article = $('#note_template').clone();
			article.attr('id', 'note_id_'+data.note.id );
			article.find('.content').html(data.note.note);
			article.find('footer span').html('Created by ' + data.note.creator + ' on ' + formatDate(data.note.atime) )
			article.find('.edit_button, .trash_button').attr('data-note_id', data.note.id );
			article.insertAfter('#note_template');
		}, 'json');
	});
	
	// delete note permenantly
	$('#content').on('click', '.note .delete_permenantly', function() {
		var id = $(this).attr('data-note_id');
		$.post( OC.filePath('projects', 'ajax', 'edit_note.php'), "delete_note_permenantly="+id, function(data) {
			$.each(data.note.id, function(i, value) {
				$('#note_id_'+value).remove();
			});
		}, 'json');
	});
	
	
	/* ------------------------
	 * People
	 * -----------------------*/
	
	$('#content').on('change', '#people li input', function() {
		var person = $(this).parent().parent();
		if ( person.hasClass('creator') ) return false; 
		var uid = person.attr('data-uid'), project_id = $('#people').attr('data-project_id')
		$.ajax(
			{
				url: OC.filePath('projects', 'ajax', 'edit_people.php'), 
				data: "toggle_uid="+uid+"&project_id="+project_id,
				type: "POST",
				dataType: 'json',
				beforeSend: function() {
					person.find('.loading_person').show();
				},
				success: function(data) {
					person.find('.loading_person').hide();
					if (data.current_user) {
						$('#people li[data-uid="'+uid+'"]').addClass('checked');
					} else {
						$('#people li[data-uid="'+uid+'"]').removeClass('checked');
					}
				}
			}
		);
	})
	
	
	/* ------------------------
	 * History
	 * -----------------------*/

	var loading_events = false;

	$('#content').on('click', '#load_more_events', function() {
		if (loading_events) return false;

		var project_id = $(this).attr("data-project_id"), offset = $(this).attr("data-offset");
		
		$.ajax({
			url: OC.filePath('projects', 'ajax', 'load_history.php'),
			type: 'POST',
			data: "project_id=" + project_id + "&offset=" + offset,
			beforeSend: function() {
				loading_events = true;
				$('#load_more_events').html("<i class='icon-download'></i> Loading...");
			},
			success: function(data) {
				if (data) {
					$('#load_more_events').before(data);
					$('#load_more_events').html("<i class='icon-download'></i> Load more events").attr("data-offset", parseInt(offset)+20);
					loading_events = false;
				} else {
					$('#load_more_events').html("<i class='icon-download'></i> No more events to load");
				}
			}
		});
	});

	/* ------------------------
	 * Files
	 * -----------------------*/
	
	$('#content').on('change', '#file_upload_start', function() {
		var data = new FormData(document.getElementById('upload-form'));
		console.log(data);
		$.ajax({
			url: OC.filePath('files', 'ajax', 'upload.php'),
			type: 'POST',
			data: data,
			dataType: 'JSON',
			success: function(data){ console.log(data)},
			contentType: false,
			processData: false
		});
		
	});
	


});
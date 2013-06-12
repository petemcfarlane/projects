$(document).ready(function(){
	
	/*
	 * PJAX
	 */ 
	
	if ($.support.pjax) {
		$(document).pjax('#content a', '#content'); // every 'a' within '#content' to be clicked will load the content in '#content'
	}
	
	/*
	 * Globals
	 */
	
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
			month[0]="January";
			month[1]="February";
			month[2]="March";
			month[3]="April";
			month[4]="May";
			month[5]="June";
			month[6]="July";
			month[7]="August";
			month[8]="September";
			month[9]="October";
			month[10]="November";
			month[11]="December";
			
			today = new Date();
			today.setHours(00,00,00);
			fortnight = new Date(today);
			fortnight.setDate(fortnight.getDate()+14);
			date = new Date(date);
			if ((date > today) && (date < fortnight)) {
				return(weekday[date.getDay()] + ", " + month[date.getMonth()] + " " + date.getDate() );
			} else {
				return(date.getDate() + "/" + (date.getMonth()+1) + "/" + date.getFullYear() );
			}
	}
	
	/*
	 * Projects
	 */
	
	// New project button clicked
	$('#content').on('click', '#new_project_button', function() {
		$.post(OC.filePath('projects', 'templates', 'part.new.php'), function(data) {
			$('#main').html(data);
			$('#new_project_form #name').focus();
		});
	});
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
				console.log(data);
				var url = OC.filePath('projects', null, 'index.php');
				$.pjax({url: url, container: '#content'});
			}, "json");
		}
	});
	
	// Show archived projects 
	$('#content').on('click', '#show_archived_projects', function() {
		$('#archived_project_list').toggle();
	});
	
	// Restore project
	$('#content').on('click', '.restore_archived_project', function() {
		$.post( OC.filePath('projects', 'ajax', 'edit_project.php'), 'restore_archived_project='+$(this).attr('data-project_id'), function(data) {
			console.log(data);
			var url = OC.filePath('projects', null, 'index.php');
			$.pjax({url: url, container: '#content'});
		}, "json");
	});
	
	/*
	 * Details
	 */

	// Ajax edit details
	$('#content').on("change", "#edit_details input, #edit_details textarea, #edit_details select", function() {
		var post = $(this).serialize()+'&id='+($('#project_id').val())
		$.post( OC.filePath('projects', 'ajax', 'edit_project.php'), post, function(data) {
			console.log(data);
		}, "json");
	});

	/*
	 * Tasks
	 */
	
	// Ajax add task
	$('#content').on('click', '#new_task_button', function() {
		$(this).fadeOut("fast");
		$('#new_task').fadeIn("fast").draggable();
		$('#new_summary').focus();
	});
	$('#content').on("submit", '#new_task', function(event) {
		event.preventDefault();
		var post = $(this).serialize();
		$.post( OC.filePath('projects', 'ajax', 'edit_task.php'), post, function(data) {
			$('#calendar_id').val(data['calendar_id']);
			$('#tasks').append('<tr class="task" data-task_id="' + data.task['id'] + '">' 
				+ '<td><input data-task_id="' + data.task['id'] + '" class="complete_checkbox" type="checkbox" name="complete" /></td>'
				+ '<td class="priority"></td>'
				+ '<td class="task_content"><h2 class="task_summary"></h2><p class="task_description"></p></td>'
				+ '<td class="due-date" data-due="" data-assign=""></td>'
				+ '<td class="completed-date"></td>'
			+ '</tr>');
			populateTask(data.task);
			
			$('#new_task').hide();
			$('#new_task_button').show();
		}, "json");
	});
	$('#content').on('click', '#cancel_new_task', function() {
		$('#new_task').fadeOut("fast");
		$('#new_task_button').fadeIn("fast");		
	});
	
	// Ajax complete task
	$('#content').on("change", "#tasks .complete_checkbox", function() {
		var checked = 0;
		if ( $(this).is(':checked') ) checked = 100;
		var post = "id="+$(this).attr("data-task_id")+"&type=complete&checked="+checked+"&project_id="+$('#project_id').val();
		$.post( OC.filePath('projects', 'ajax', 'edit_task.php'), post, function(data) {
			var task = $("#tasks").find("[data-task_id='" + data.data['id'] + "']");
			if (data.data.complete == "100") {
				completed = new Date(data.data.completed);
				task.find('.completed-date').html('Completed ' + formatDate(data.data.completed) + '<br /> By ' + data.data.completed_by );
			} else {
				task.find('.completed-date').text('');
			}
		}, "json");
	});

	// Ajax edit task
	var task = new Array();
	$("#content").on("click", "#tasks .task_summary", function() {
		// get task info
		task = [];
		task['id'] = $(this).parent().parent().attr('data-task_id');
		task['summary'] = $(this).text();
		task['due'] = $(this).parent().parent().find('.due-date').attr('data-due');
		task['assign'] = $(this).parent().parent().find('.due-date').attr('data-assign');
		task['priority'] = $(this).parent().parent().find('.priority').attr('data-priority');
		task['description'] = $(this).next().text();
		
		// populate form
		$('#task_id').val(task['id']);
		$('#summary').val(task['summary']);
		$('#due').val(task['due']); 
		$('#assign').val(task['assign']);
		$('#priority').val(task['priority']);
		$('#notes').val(task['description']);
		
		// show form
		$('#edit_task').fadeIn("fast").draggable();
	});
	// save and update, on update
	$('#content').on('click', '#update_task', function(e){
		e.preventDefault();
		post = $('#edit_task').serialize() + "&type=update";
		$.post( OC.filePath('projects', 'ajax', 'edit_task.php'), post, function(data) {
			// update task on page
			populateTask(data.data);
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
		var r = confirm("Are you sure you want to delete this task? This operation cannot be undone.");
		if (r == true) {
			$.post( OC.filePath('projects', 'ajax', 'edit_task.php'), "id="+ task['id'] + "&type=delete", function(data) {
				$("#tasks").find("[data-task_id='" + data.data['id'] + "']").remove();
			}, "json");
		}
		$('#edit_task').fadeOut("fast");
	});
		
	// populate the task from json data
	function populateTask(data) {
		var task = $("#tasks").find("[data-task_id='" + data['id'] + "']");
		task.find(".task_summary").text(data.summary);
		if (data.due) {
			var due = new Date(parseInt(data.due)*1000);
			task.find(".due-date").text("Due " + formatDate(parseInt(data.due)*1000) );
			task.find(".due-date").attr("data-due", due.getFullYear() + "-" + ("0" + (due.getMonth()+1)).slice(-2) + "-" + ("0" + due.getDate()).slice(-2));
		} else {
			task.find(".due-date").text("");
			task.find(".due-date").attr("data-due", "");
		}
		if (data.assigned_to) task.find(".due-date").append("<br />Assigned to " + data.assigned_to);
		task.find(".due-date").attr("data-assign", data.assigned_to);
		var priority = parseInt(data.priority);
		if (priority < 4 ) {
			task.find(".priority").text('!!!').attr('data-priority', priority);
		} else if(priority > 3 && priority < 7 ) {
			task.find(".priority").text('!!').attr('data-priority', priority);
		} else if (priority > 7 ) {
			task.find(".priority").text('!').attr('data-priority', priority);
		} else {
			task.find(".priority").text('').attr('data-priority', '');
		}
		task.find('.task_description').text(data.description);		
	}

	/*
	 * Notes
	 */
	
	// on add new note
	$('#content').on('click', '#new_note_button', function() {
		$('#new_note').fadeIn("fast").draggable();
		$('#note').focus();
	});
	$('#content').on('submit', '#new_note', function(e) {
		e.preventDefault();
		$.post( OC.filePath('projects', 'ajax', 'edit_note.php'), $(this).serialize(), function(data) {
			if (data.status == "success") {
				var article = $('#note_template').clone();
				article.attr('id', 'note_id_'+data.note.note_id );
				article.find('.content').html(data.note.note);
				article.find('footer span').html('Created by ' + data.note.creator + ' on ' + formatDate(data.note.atime) )
				article.find('.edit_button, .trash_button').attr('data-note_id', data.note.note_id );
				article.insertAfter('#note_template');
				$('#new_note').fadeOut("fast");
			} else {
				console.log(data);
			}
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
				article.attr('id', 'note_id_'+data.note.note_id );
				article.find('.content').html(data.note.note);
				article.find('footer span').html('Updated by ' + data.note.creator + ' on ' + formatDate(data.note.atime) )
				article.find('.edit_button, .trash_button').attr('data-note_id', data.note.note_id );
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
			article.attr('id', 'note_id_'+data.note.note_id );
			article.find('.content').html(data.note.note);
			article.find('footer span').html('Trashed by ' + data.note.creator + ' on ' + formatDate(data.note.atime) )
			article.find('.restore, .delete_permenantly').attr('data-note_id', data.note.note_id );
			article.insertAfter('#trash_template');
			$('#note_id_'+id).fadeOut("fast").remove();
		}, 'json');
	});
	
	// show trashed notes
	$('#content').on('click', '#show_trash', function() {
		$('.trash').not('#trash_template').fadeToggle('fast');		
	});
	
	// restore note
	$('#content').on('click', '.note .restore', function() {
		var id = $(this).attr('data-note_id');
		$.post( OC.filePath('projects', 'ajax', 'edit_note.php'), "restore_note_id="+id, function(data) {
			$('#note_id_'+data.note.note_id).remove();
			var article = $('#note_template').clone();
			article.attr('id', 'note_id_'+data.note.note_id );
			article.find('.content').html(data.note.note);
			article.find('footer span').html('Created by ' + data.note.creator + ' on ' + formatDate(data.note.atime) )
			article.find('.edit_button, .trash_button').attr('data-note_id', data.note.note_id );
			article.insertAfter('#note_template');
		}, 'json');
	});
	
	// delete note permenantly
	$('#content').on('click', '.note .delete_permenantly', function() {
		var id = $(this).attr('data-note_id');
		$.post( OC.filePath('projects', 'ajax', 'edit_note.php'), "delete_note_permenantly="+id, function(data) {
			$.each(data.note.note_id, function(i, value) {
				$('#note_id_'+value).remove();
			});
		}, 'json');
	});

	/*
	 * Files
	 */
	
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
	
	/*$('#project_menu button').click(function() {
		$('.view_section').hide();
	});

	$('#view_details').click(function() {
		$('#details').show();
	});

	$('#view_people').click(function() {
		$('#people').show();
	});

	$('#view_aims').click(function() {
		$('#aims').show();
	});

	$('#view_timeline').click(function() {
		$('#timeline').show();
	});

	$('#view_order').click(function() {
		$('#order').show();
	});

	$('#view_meetings').click(function() {
		$('#meetings').show();
		$.post (OC.filePath('projects', 'ajax', 'view_meetings.php'), 'id='+$('#project_id').val(), function(data) {
			$('#meetings_list').empty();
			$.each(data, function(i, meeting){
				$('#meetings_list').append('<a class="select_meeting" data-id="' + data[i]['id'] + '">' + data[i]['date'] + '</a>');
			});
		}, "json");
	});

	$('.ajax_update input, .ajax_update textarea').change(function() {
		var post = $(this).serialize()+'&id='+($('#project_id').val())
		$.post( OC.filePath('projects', 'ajax', 'update_project.php'), post, function(data) {
			console.log(data);
		}, "json");
	});
	
	$('#add_meeting').click(function() {
		$('#add_meeting_form').show();
	});

	$('#add_meeting_form').submit(function(event) {
		event.preventDefault();
		var post = $('#add_meeting_form').serialize();
		$.post (OC.filePath('projects', 'ajax', 'add_meeting.php'), post, function(data) {
			console.log(data);
		});
	});
	
	$('#meetings_list').on('click', '.select_meeting', function(event) {
		event.preventDefault();
		$.post (OC.filePath('projects', 'ajax', 'view_meetings.php'), 'meeting_id='+$(this).attr('data-id'), function(data) {
			console.log(data);
			$.each(data, function(key, value) {
				$('#meetings_display').append('<p><label>' + key + '</label>: ' + value + '</p>');
			});
		}, "json");
	});

	$('#project_name_label').click(function() {
		$('#project_name_label').hide();
		$('#project_name').show();
		$('#project_description_label').hide();
		$('#project_description').show();
		$('#project_name').blur(function() {
			$('#project_name_label').html($('#project_name').val()).show();
			$('#project_name').hide();
		});
		$('#project_description').blur(function() {
			$('#project_description_label').html($('#project_description').val()).show();
			$('#project_description').hide();
		});
	})
	
	$('#project_description_label').click(function() {
		$('#project_description_label').hide();
		$('#project_description').show();
		$('#project_description').blur(function() {
			$('#project_description_label').html($('#project_description').val()).show();
			$('#project_description').hide();
		});
	})
	
	$('.invitees').on('focus','.invitee input',function() {
		$(this).parent().addClass('focus');
	});

	$('.invitees').on('blur','.invitee input',function() {
		$(this).parent().removeClass('focus');
		if ($(this).val().length > 0) {
			$(this).parent().removeClass('blank');
		} else {
			$(this).parent().addClass('blank');
		}
	});

	$('#new_project_form').on('click','.remove', function() {
		$(this).parent().animate({opacity:0}, 300).animate({height:0},200, function() {$(this).remove();});
	});
	
	$('#new_project_form #name').focus();
	
	$('#new_project_form').on('keyup','.invitee input', function(event) {
		if (event.which == 13 || event.which == 40 || event.which == 38 || event.which == 9) {
			return false;
		}

		var input = $(this);

		if ( input.val().length > 1 ) {
			$.post (OC.filePath('projects', 'ajax', 'lookup_users.php'), 'name='+$(this).val(), function(data) {
				$(input).parent().find('.suggestions').empty();
				if (data['groups'].length > 0) {
					$.each(data['groups'], function(i) {
						$(input).parent().find('.suggestions').append("<li>"+data['groups'][i]['gid']+"</li>");
					});
				}
				if (data['users'].length > 0) {
					$.each(data['users'], function(i) {
						$(input).parent().find('.suggestions').append("<li>"+data['users'][i]['uid']+"</li>");
					});
				}
				input.parent().find('.suggestions li:first-child').addClass('selected');
			}, "json");

		} else {

			input.parent().find('.suggestions').empty();

		}
		
	});
	
	$('#new_project_form').on('click','.suggestions li', function() {
		$(this).parent().parent().find('input').val($(this).html());
		$(this).parent().parent().next().find('input').focus();
		$(this).parent().empty();
		addInvitee();
	});

	$('#new_project_form').on('keydown','.invitee input', function(event) {
		if ( event.which == 13 || event.which == 9 ) { // return or tab
			event.preventDefault();
			$(this).val($(this).parent().find('.suggestions li.selected').html());
			$(this).parent().next().find('input').focus();
			$(this).parent().find('.suggestions').empty();
			addInvitee();
		}
		
		if (event.which == 40) { //down
			var old = $('.suggestions li.selected');
			if (old.next().length > 0 ) {
				old.removeClass('selected');
				old.next().addClass('selected');
			}
		}

		if (event.which == 38) { //up
			var old = $('.suggestions li.selected');
			if (old.prev().length >0 ) {
				old.removeClass('selected');
				old.prev().addClass('selected');
			}
		}
	});

	function addInvitee() {
		if ( $('.invitees .blank').length < 2 ) {
			$('.invitees').append('<div class="person invitee blank"><div class="icon"></div><input name="users[]" type="text" autocomplete="off" /><a class="remove"></a><ul class="suggestions"></ul></div>');
		}
	}

	$('.invitees').on('hover', '.suggestions li', function() {
		$('.suggestions li.selected').removeClass('selected');
		$(this).addClass('selected');
	});
	

	// Sidebar, show closed projects
	$('#closed_title').toggle(function() {
		$('ul#projects').animate({bottom: "22em"});
		$('#closed_projects_wrapper').animate({height: "25em"});
	}, function() {
		$('#closed_projects_wrapper').animate({height: "3em"});
		$('ul#projects').animate({bottom: "0"});
	});
	
*/



});
$(document).ready(function () {
	
	// call init() when the document is ready - for every direct load.
	// It must also be called after every pjax request
	
	init(); 

	/* ------------------------
	 * PJAX
	 * -----------------------*/
	
	if ($.support.pjax) {
		$(document).pjax('#content a', '#content'); // every 'a' within '#content' to be clicked will load the content in '#content'

		$(document).on('submit', '#content form', function(e) {
			$.pjax.submit(e, '#content');
		});
		
		$('#content').on('pjax:end', init );
	}
	
});

function init() {

	/* ------------------------
	 * Projects
	 * -----------------------*/

	$('#show-new-project-form').on('click', function() {
		$(this).fadeOut();
		$('#create-project').parent().slideDown();
		$('#projectName').focus();
		$('#cancel-new-project').on('click', function() {
			$('#projectName').val('');
			$('#create-project').parent().slideUp();
			$('#show-new-project-form').fadeIn();
		});
	});

	/* ------------------------
	 * Details
	 * -----------------------*/
    
    $('.illusion').autosize();

    $('#new-detail-value').autosize();

    $('#new-detail-key').val('').hide();
    $('#new-detail-value').val('').hide();
    $('#detail-key-holder').text('').hide();
    $('#add-detail-submit').hide();
    $('#select-add-field').change(function() {
        var field = $(this).val();
        if (field === "Other...") {
            $('#new-detail-key').val('').show().focus();
            $('#detail-key-holder').text('');
            $('#new-detail-value').show();
            $('#add-detail-submit').show();
        } else if (field === "Add field") {
            $('#new-detail-key').val('').hide();
            $('#new-detail-value').val('').hide();
            $('#detail-key-holder').text('').hide();
            $('#add-detail-submit').hide();
        } else {
            $('#new-detail-key').val( $(this).val() ).hide();
            $('#detail-key-holder').text( $(this).val() ).show();
            $('#add-detail-submit').show();
            $('#new-detail-value').show().focus();
        }
    });
    
    $.each( $('input.detail-key'), function(i, item) {
        $("#select-add-field option[value='"+item.value+"']").remove();
    });

	/* ------------------------
	 * Notes
	 * -----------------------*/

    $('.paper textarea').autosize();
    $('#edit-note, #new-note').submit(function(e) {
    	e.preventDefault();
    	$('#save-note').val('Saving...');
    	$('#note-input').val($('#note-content').html());
    });
    $('#new-note').find('article').focus();
    $('#note-content').one('keyup', function() {
    	$('.note-status').text('Unsaved changes');
    	$('#save-note').show();
    });
}

$(document).ready(function () {
	
	/* ------------------------
	 * PJAX
	 * -----------------------*/
	
	// if ($.support.pjax) {
		// $(document).pjax('#content a', '#content'); // every 'a' within '#content' to be clicked will load the content in '#content'
	// }
	
    // $('.project-name').click(function(){
        // $(this).parent().find('.block').slideToggle('fast');
    // });
	
    /*
     * Automatically resize textarea inputs
     */
    
    $('textarea.illusion').autosize();

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
        // console.log(item.value);
    });

});
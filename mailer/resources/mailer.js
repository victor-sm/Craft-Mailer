$(document).ready(function() {
	
	//Redactor
	$('#mailer_htmlBody').redactor({
		//toolbarFixed: true,
		minHeight: 125,
		buttons: ['html', '|', 'formatting', '|', 'underline', 'bold', 'italic', 'deleted', '|', 'unorderedlist', 'orderedlist', 'outdent', 'indent', '|', 'link', 'image', 'video', '|', 'alignment', 'horizontalrule']
	});
	$('#log_htmlBody').redactor({
		//toolbarFixed: true,
		minHeight: 125,
		buttons: ['html']
	});


	//VariableList
	$('#variable_list').hide();

	$('#variable_btn').click(function() {
		$('#variable_list').slideToggle();
	});


	//Log Refresh
	$('#refresh_log').click(function() {
		window.location.href = $(this).attr('data-url');
	});

});
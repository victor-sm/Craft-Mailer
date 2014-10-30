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
	
	
	//AddToRedactor
	$('.addToRedactor').click(function(event) {
		event.preventDefault();

		var addText = $(this).text();
		//Change 'insertText' to 'insert.text' for Redactor 10
		$('#mailer_htmlBody').redactor('insertText', addText);
	});


	//Log Refresh
	$('#refresh_log').click(function() {
		window.location.href = $(this).attr('data-url');
	});

});

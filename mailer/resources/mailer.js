$(document).ready(function() {
	
	//Redactor
	$('#mailer_htmlBody').redactor({
		//toolbarFixed: true,
		minHeight: 125,
		buttons: ['html', '|', 'formatting', '|', 'underline', 'bold', 'italic', 'deleted', '|', 'unorderedlist', 'orderedlist', 'outdent', 'indent', '|', 'link', 'image', 'video', '|', 'alignment', 'horizontalrule']
	});


	//VariableList
	$('#variable_list').hide();

	$('#variable_btn').click(function() {
		$('#variable_list').slideToggle();
	});

});
function cbFormNotice(success, message){
	$rdks.alert.notice({
		result: success,
		buttons: {
			large: true
		},
		elastic: true,
		theme: "dark",
		message: message
	});
}

function cbFormRedirectSuccess(response){
	if(response.success) {
		$rdks.http.redirect(response.data.url_redirect);
	} else {
		cbFormNotice(response.success, response.message);
	}
}

function cbFormSuccess(response){
	cbFormNotice(response.success, $rdks.html.tag('b', response.message));
}

function cbFormLoading(){
	$rdks.modal.loader(true);
}

function cbFormError(){
	cbFormNotice(false, "There was an error!");
}

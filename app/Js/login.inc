function cbFormSuccess(response, status, xhr, formObj){
	$rdks.modal.loader(false);

	if(response.success) {
		$rdks.http.redirect("/");
	} else {
		$rdks.form.reset(formObj, false);
	}
}

function cbFormLoading(xhr, settings, formObj){
	$rdks.modal.loader(true);
}

function cbFormError(xhr, ajaxOptions, thrownError, formObj){
	$rdks.modal.loader(false);
	console.log("There was an error while processing the request.");
}
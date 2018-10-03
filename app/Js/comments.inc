function cbFormSuccessComments(response){

  if (response.success) {
		$rdks.modal.loader(false);
		$rdks.each("comments")
		.append([
				{
					picture: response.data.picture,
					name: response.data.name,
					date: response.data.date,
					comment: response.data.comment
				}
		]).render();
	} else {
		$rdks.http.redirect('/login');
	}

}

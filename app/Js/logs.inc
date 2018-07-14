function userLogs(id, date){

	$rdks.http.getHtml('/_block/_user/logs', {id: id, date: date, rdks: 1}, function(block){

		$rdks.modal.open({
			title: "Logs",
			header: true,
			footer: true,
			width: 960,
			theme: 'dark',
			content: block
		});

	}, function(){
		$rdks.modal.loader(true);
	},function(){

		$rdks.alert.notice({
			result: false,
			elastic: true,
			title: "Error",
			message: "There was an error.",
		});

	});

}

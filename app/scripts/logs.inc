function userLogs(id, date){

	$rdks.http.getHtml('/_block/_user/logs', {id: id, date: date}, function(block){

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
	});

}
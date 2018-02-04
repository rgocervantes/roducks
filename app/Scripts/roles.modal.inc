function modalRoles(title, config, type){

	$rdks.http.getHtml('/_block/_roles/modal', {rdks:1, config: config, type: type}, function(block){

		$rdks.modal.open({
			title: title,
			header: true,
			footer: true,
			width: 960,
			content: block,
			theme: 'dark',
			onOpen: function(){
				$(".checkbox-permissions").click(function(e){
					e.preventDefault();
				});
			}
		});

	}, function(){
		$rdks.modal.loader(true);
	});

}
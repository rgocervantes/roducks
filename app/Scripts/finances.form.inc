
function cbFormSimulationSuccess(response){
	if(response.success){

		$rdks.http.postHtml('<?php echo $tpl['url']; ?>', {fields: response.data.fields}, function(html){
			$rdks.modal.open({
				title: "Simulación",
				width: 540,
				content: html,
				onClose: function(){
					$rdks.form.getFieldByName('total').value = "";
				}
			});
		});

	} else {
		cbFormNotice(response.success, response.message);
	}
}
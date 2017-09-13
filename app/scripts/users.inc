<?php
use rdks\core\framework\Language;
?>
function cbFormUsersSuccess(response){
	if(response.success){
		$rdks.http.redirect("<?php echo $url; ?>/picture/id/"+response.data.insert_id);
	} else {
		$rdks.modal.loader(false);
	}
}

function cbLogout(btn, response){
	if(response.success){
		$(btn)
		.removeClass("btn-info")
		.addClass("btn-default disabled")
		.removeAttr('rel');
	}
}

function cbChoiceRole(text, value, data){
	modalRoles(data.title, data.config, data.type);
}

function cbRemove(btn, params){

	var count = parseInt($("#trash-count").text()),
		totals = parseInt($("#totals").text());

		count++;
		totals--;

		$("#trash-count").text(count);
		$("#totals").text(totals);

	dataGrid.remove(btn, params);	
}

function userCard(id){
	$rdks.http.getHtml('/_block/_user/card/'+id, {rdks:1}, function(block){

		$rdks.modal.open({
			header: true,
			footer: true,
			width: 640,
			content: block,
			elastic: true,
			theme: 'dark'
		});

	}, function(){
		$rdks.modal.loader(true);
	});
}

function userId(id){
	$rdks.alert.notice({
		title: 'ID',
		elastic: true,
		theme: 'dark',
		message: $rdks.html.tag('h3', id),
		buttons: {
			large: true,
			close: {
				label: "<?php echo TEXT_CLOSE; ?>",
				style: "btn btn-default"
			}
		}
	});
}

var calendar;

function expirationDate(id_user, expDate){

	var selectedDate = $rdks.date.getDateArray(expDate);

	$rdks.modal.open({
		header: true,
		width: 420,
		height: 360,
		elastic: true,
		theme: 'dark',
		content: $rdks.html.div('',{"id":"calendar"}),
		onOpen: function(){

			calendar = new $rdks.calendar();
			calendar.init({
					instance: 'calendar',
					id: '#calendar',
					lang: 'es',
					speller: 1,
					year: selectedDate[0],
					month: selectedDate[1],
					day: selectedDate[2],
					display: {
						title: true,
						ctrls: true,
						months: true,
						years: true
					},
					futureDate: true,
					availableDay: true,
					onSelectDay: function(y, m, d, isEvent, e, t){
						var date = $rdks.date.getDateToYMD([y,m,d]),
							datef = $rdks.date.getDateFormat([y,m,d], false, '<?php echo Language::get(); ?>');

						$rdks.http.postJson('/_json<?php echo $url; ?>/expiration', {id: id_user, date: date}, function(res){
							$("#exp-"+id_user).text(datef);
							$rdks.modal.close(true); // it'll close pop modal as well
						}, function(){
							$rdks.alert.pop({
								message: "<?php echo TEXT_ONE_MOMENT_PLEASE; ?>"
							});
						});
					}			
			});

		}
	});

}


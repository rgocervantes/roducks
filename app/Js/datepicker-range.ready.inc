<?php use Roducks\framework\Language; ?>
	$rdks.datepicker({
		from: {
			id: "#start_date",
			onSelectDay: function(y, m, d, isEvent, e, t){
				//console.log($rdks.date.getDateFormat([y, m, d], true, 'es'));
			}
		},
		to: {
			id: "#end_date",
			onSelectDay: function(y, m, d, isEvent, e, t){
				//console.log($rdks.date.getDateFormat([y, m, d], true, 'es'));
			}
		},
		format: "dd/mm/yyyy",
		lang: "<?php echo Language::get(); ?>",
		speller: 3,
		display: {
			title: true,
			ctrls: true,
			months: true,
			years: true
		}		
	});
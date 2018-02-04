	$finances.getTotal();
	
	dataGrid.init({
		id: "#data-grid",
		fixed: false,
		container: 'container-fluid',
		text: {
			remove: "El registro se eliminará permanentemente."
		},
		modal: {
			buttons: {
				cancel: {
					label: 'Cancelar',
					style: 'btn btn-warning',
					iconLeft: 'remove'
				},
				accept: {
					label: 'Aceptar',
					style: 'btn btn-success',
					iconLeft: 'ok'
				}			
			}			
		}
	});
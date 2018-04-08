	var allowedType = [
			'jpg'
		];

	var size = <?php echo \Roducks\Page\Block::getData("user")->getUploadSize(); ?>;	

	uploader.init({
		id: '#btn-upload', // .btn-upload
		name: 'uploader',
		type: allowedType,
		size: size, // Max
		button: [
			Math.floor($("#btn-upload-box").width()),
			$("#btn-upload-box").height()
		], // size
		url: '/_service/storage/file/upload/global/user',
		onClick: function(){
			$rdks.modal.loader(true);
		},
		onSelect: function(){
			$rdks.modal.loader(false);
		},
		onUpload: function(response){

			if(response.success) {
				$rdks.modal.loader(true);
				$('#crop').html( $rdks.html.img( response.data.dir + response.data.file ) );
				$("#dir").val( response.data.dir );
				$("#img").val( response.data.file );
				$("#cropped").html('');

				setTimeout(function(){
					cropInit();
					$rdks.modal.loader(false);
					$("#btn-upload-box").addClass('display-none');
					$("#btn-crop").removeClass('display-none');
				}, 200);

			} else {

				$rdks.alert.notice({
					result: response.success,
					message: response.message,
					elastic: true,
					buttons: {
						large: true
					}
				});
			}

		},
		onError: function(code, message, data){

			var message; 
			
			switch(code){
				case 1:
					// Invalid Type
					message = "Formato inválido: '"+data.type+"', Sólo se permite: "+allowedType.join(", ");
				break;
				case 2:
					// File too heavy
					message = "El archivo es muy grande, Por favor suba uno menor a: "+(size[0]+1)+" "+size[1];
				break;
			}

			$rdks.alert.notice({
				result: false,
				message: message,
				elastic: true,
				buttons: {
					large: true
				}
			});

		}
	});

function cropInit(){

	$('#x').val(0);
	$('#y').val(0);
	$('#w').val(0);
	$('#h').val(0);

	$('#crop').Jcrop({
		aspectRatio: 1, //set this for square, comment this if you want an irregular figure.
		onSelect: function(c){
			
			$('#x').val(c.x);
			$('#y').val(c.y);
			
			var w = $(".jcrop-tracker").width();
			var h = $(".jcrop-tracker").height();
			
			$('#w').val(w);
			$('#h').val(h);

		}
	});	

}

function cropComplete() {
	$rdks.modal.loader(false);

	$(".jcrop-holder").before('<div id="crop"></div>').remove();
	$("#btn-upload-box").removeClass('display-none');
	$("#btn-crop").addClass('display-none');
}

function cbCropSuccess(response) {
	
	if(response.success) {
		var img = response.data.dir + response.data.img;
		var file = response.data.dir + response.data.cropper;
		$("#cropped").html( $rdks.html.a('javascript:;', $rdks.html.img( img ) , {"onclick":"$rdks.alert.image({\"src\": \""+file+"\"});"}) );

		$rdks.http.postJson('<?php echo $url_json_picture; ?>', {picture: response.data.name}, function(response){
			cropComplete();
		});

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

}

function cbCropError() {

	cropComplete();

	$rdks.alert.notice({
		result: false,
		message: 'Something went wrong!',
		elastic: true,
		buttons: {
			large: true
		}
	});
}

var uploader = new $rdks.uploader();
			
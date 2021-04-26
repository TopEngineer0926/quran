$(document).ready(function(){	
	$('#changeProfilePhoto').on('click', function(e){
        $('#changeProfilePhotoModal').modal({show:true});        
    });	
	$('#profileImage').on('change', function()	{ 
		$("#previewProfilePhoto").html('');
		$("#previewProfilePhoto").html('Uploading....');
		$("#cropImage").ajaxForm(
		{
		target: '#previewProfilePhoto',
		success:    function() { 
				$('img#photo').imgAreaSelect({
				aspectRatio: '1:1',
				onSelectEnd: getSizes,
			});
			$('#imageName').val($('#photo').attr('file-name'));
			}
		}).submit();

	});	
	$('#savePhoto').on('click', function(e){
    e.preventDefault();
    params = {
            targetUrl: 'change_photo.php?action=save',
            action: 'save',
            x_axis: $('#hdn-x1-axis').val(),
            y_axis : $('#hdn-y1-axis').val(),
            x2_axis: $('#hdn-x2-axis').val(),
            y2_axis : $('#hdn-y2-axis').val(),
            thumb_width : $('#hdn-thumb-width').val(),
            thumb_height:$('#hdn-thumb-height').val()
        };
        saveCropImage(params);
    });   
    function getSizes(img, obj){
        var x_axis = obj.x1;
        var x2_axis = obj.x2;
        var y_axis = obj.y1;
        var y2_axis = obj.y2;
        var thumb_width = obj.width;
        var thumb_height = obj.height;
        if(thumb_width > 0) {
			$('#hdn-x1-axis').val(x_axis);
			$('#hdn-y1-axis').val(y_axis);
			$('#hdn-x2-axis').val(x2_axis);
			$('#hdn-y2-axis').val(y2_axis);
			$('#hdn-thumb-width').val(thumb_width);
			$('#hdn-thumb-height').val(thumb_height);
        } else {
            alert("Please select portion..!");
		}
    }   
    function saveCropImage(params) {
		$.ajax({
			url: params['targetUrl'],
			cache: false,
			dataType: "html",
			data: {
				action: params['action'],
				id: $('#hdn-profile-id').val(),
				t: 'ajax',
				w1:params['thumb_width'],
				x1:params['x_axis'],
				h1:params['thumb_height'],
				y1:params['y_axis'],
				x2:params['x2_axis'],
				y2:params['y2_axis'],
				imageName :$('#imageName').val()
			},
			type: 'Post',
		   	success: function (response) {
					$('#changeProfilePhotoModal').modal('hide');
					$(".imgareaselect-border1,.imgareaselect-border2,.imgareaselect-border3,.imgareaselect-border4,.imgareaselect-border2,.imgareaselect-outer").css('display', 'none');
					console.log(response);
					$("#profilePhoto").attr('src', response);
					$("#previewProfilePhoto").html('');
					$("#profileImage").val();
					$("#changeProfilePhoto").text('Change Photo');
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert('status Code:' + xhr.status + 'Error Message :' + thrownError);
			}
		});
    }
});
const baseUrl = document.getElementsByTagName("meta").baseurl.content;
function isNumberKey(evt) {
    var charCode = evt.which ? evt.which : evt.keyCode;
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) return false;
    return true;
}
function ValidateAlpha(evt) {
    var charCode = (evt.which) ? evt.which : window.event.keyCode;
    if (charCode <= 13) {
        return true;
    }
    else {
        var keyChar = String.fromCharCode(charCode);
        var re = /^[a-zA-Z ]+$/;
        return re.test(keyChar);
    }
}
function deleteImage(code, value) {
    $.ajax({
        url: baseUrl + '/dips/deleteImage',
        method: "get",
        data: {
            'value': value,
            'code': code
        },
        datatype: "text",
        success: function (data) {
            if (data = "true") {
                location.reload();
            } else {
                alert('not deleted');
            }
        }

    });

}
$(document).ready(function () {
    $(document).on("change", "#file", function () {
        var filePath = $(this).val();
        var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;

        if (filePath != "") {
            if (!allowedExtensions.exec(filePath)) {
                toastr.error('Invalid File type', 'Soft Drinks', {
                    "progressBar": false
                });
                $(this).val(null);
                return false;
            } else {
                const file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function (event) {
                        const img = new Image();
                        img.onload = function () {
                            // Check if the image is 512x512
							
							if (img.width >= 512 && img.height >= 512) {
								$('#eDisImage').removeClass("d-none");
                                $('#eImage').addClass("d-none");
                                $('#showImage').removeClass('d-none')
                                $("#showImage").attr("src", event.target.result);
							}else{
								toastr.error('The image dimensions are not valid. Please upload an image with at least 512 x 512 dimensions.', 'Sides', {
                                    "progressBar": false
                                });
                                // Reset the input field and hide the image
                                $('#file').val(null);
								return false;
							}
							
                        };
                        img.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    });


});
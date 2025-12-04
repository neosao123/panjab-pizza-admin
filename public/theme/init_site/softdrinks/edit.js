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
        url: baseUrl + '/softdrinks/deleteImage',
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
                        // Create a new Image object to check the dimensions
                        var img = new Image();
                        img.onload = function () {
                            if (img.width >= 512 && img.height >= 512) {
                                // If dimensions are greater than 512x512, show the image
                                $('#eDisImage').removeClass("d-none");
                                $('#eImage').addClass("d-none");
                                $("#showImage").attr("src", event.target.result);
                            } else {
                                toastr.error('The image dimensions are not valid. Please upload an image with at least 512 x 512 dimensions.', 'Error', {
                                    "progressBar": false
                                });
                                $("#file").val(null);
                                $("#showImage").attr("src", '');
                                $('#eDisImage').addClass("d-none");
                                $('#eImage').addClass("d-none");
                            }
                        };
                        img.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    });
    $("#description").summernote({
        height: 100,
        styleTags: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        toolbar: [

            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']]
        ]
    });
});
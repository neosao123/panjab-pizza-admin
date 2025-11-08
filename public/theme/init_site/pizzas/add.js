const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {

    $(document).on("change", "#file", function () {
        var filePath = $(this).val();
        var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;
        if (!allowedExtensions.exec(filePath)) {
            toastr.error('Invalid File type', 'Pizza', {
                "progressBar": false
            });
            $(this).val(null);
            return false;
        } else {
            const file = this.files[0];
            if (file) {
                let reader = new FileReader();
                $('#eImage').removeClass("d-none");
                reader.onload = function (event) {
                    let img = new Image();
                    img.onload = function () {
                        if (img.width >= 512 && img.height >= 512) {
                            $("#showImage").attr("src", event.target.result);
                            toastr.success('Image uploaded successfully.', 'Success', {
								"progressBar": false
							});
						} else {
                            toastr.error('The image dimensions are not valid. Please upload an image with at least 512 x 512 dimensions.', 'Error', {
									"progressBar": false
							});
							$("#file").val(null);
                        }
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    });

    $(`select[id='cheese']`).select2({
        placeholder: "Select",
        allowClear: true

    });
    $(`select[id='crust']`).select2({
        placeholder: "Select",
        allowClear: true
    });
    $(`select[id='crustType']`).select2({
        placeholder: "Select",
        allowClear: true
    });
    $(`select[id='specialBase']`).select2({
        placeholder: "Select",
        allowClear: true
    });
    $(`select[id='spices']`).select2({
        placeholder: "Select",
        allowClear: true
    });
    $(`select[id='sauce']`).select2({
        placeholder: "Select",
        allowClear: true
    });
    $(`select[id='cook']`).select2({
        placeholder: "Select",
        allowClear: true
    });

    $("#category").select2({
        placeholder: "Select",
        allowClear: true,
        ajax: {
            url: baseUrl + "/getPizzasCategories",
            type: "get",
            delay: 250,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
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
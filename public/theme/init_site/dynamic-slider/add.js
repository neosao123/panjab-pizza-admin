const baseUrl = document.getElementsByTagName("meta").baseurl.content;
const tblBody = $("#tbl-address tbody");
const noOfAddress = 5;
const count = $("input[name='rowCount']");
const inFrom = $("form[id='inform']");

function getMaxOfArray(numArray) {
    return Math.max.apply(null, numArray);
}
function addRow() {
    var length = $("#tableBody tr").length;
    var arr = [];
    $("#tableBody tr").each(function () {
        arr.push(this.id.substring(3));
    });
    var rowCount = getMaxOfArray(arr);
    if (rowCount == "-Infinity") {
        rowCount = 0;
    } else {
        rowCount++;
    }

    if (noOfAddress == length) {
        toastr.error("Can't be added more than 5 store address", "Error", {
            preventDuplicates: true,
            progressBar: true,
        });
        return false;
    }

    var html = `
        <tr id="row${rowCount}" class="tblrows" data-type="">
            <td>
                <input type="text" id="store_address${rowCount}" name="store_address[]" class="form-control" required value="" data-parsley-required-message="Store Address is required" data-parsley-minlength="2" data-parsley-minlength-message="You need to enter at least 2 characters" data-parsley-trigger="change">               
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger del-button" data-row-id="row${rowCount}"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
    `;
    tblBody.append(html);
    count.val(rowCount);
    inFrom.parsley("refresh");
}

$(document).on("click", "button.add-address", function (e) {
    e.preventDefault();
    addRow();
});


$(document).on("change", "#background_image", function () {
    var filePath = $(this).val();
    var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;

    if (!allowedExtensions.exec(filePath)) {
        toastr.error('Invalid file type. Please upload a JPEG or PNG image.', 'Large Image', {
            "progressBar": false
        });
        $(this).val(null);
        $('#showImage').addClass('d-none');
        return false;
    } else {
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();
            $('#eImage').removeClass("d-none");
            reader.onload = function (event) {
                const img = new Image();

                img.onload = function () {
                    if (img.width >= 1280 && img.height >= 480) {
                        $("#showImage").attr("src", event.target.result);
                        $('#showImage').removeClass('d-none');
                    } else {
                        toastr.error('Invalid image dimensions. The image size is less than 1280 X 480 pixels.', 'Large Image', {
                            "progressBar": false
                        });
                        $(document).find('#background_image').val(null);
                        $('#showImage').attr('src', '');
                        $('#showImage').addClass('d-none');
                    }
                };
                img.onerror = function () {
                    toastr.error('Error loading image.', 'Large Image', {
                        "progressBar": false
                    });
                    $(document).find('#background_image').val(null);
                    $('#showImage').attr('src', '');
                    $('#showImage').addClass('d-none');
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
});

$(document).on("change", "#background_image_md", function () {
    var filePath = $(this).val();
    var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;

    if (!allowedExtensions.exec(filePath)) {
        toastr.error('Invalid file type. Please upload a JPEG or PNG image.', 'Medium Image', {
            "progressBar": false
        });
        $(this).val(null);
        $('#showImageMd').addClass('d-none');
        return false;
    } else {
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();
            $('#eImageMd').removeClass("d-none");
            reader.onload = function (event) {
                const img = new Image();

                img.onload = function () {
                    if (img.width >= 720 && img.height >= 648) {
                        $("#showImageMd").attr("src", event.target.result);
                        $('#showImageMd').removeClass('d-none');
                    } else {
                        toastr.error('Invalid image dimensions. The image size is less than 720 X 648 pixels.', 'Medium Image', {
                            "progressBar": false
                        });
                        $(document).find('#background_image').val(null);
                        $('#showImageMd').attr('src', '');
                        $('#showImageMd').addClass('d-none');
                    }
                };
                img.onerror = function () {
                    toastr.error('Error loading image.', 'Medium Image', {
                        "progressBar": false
                    });
                    $(document).find('#background_image').val(null);
                    $('#showImageMd').attr('src', '');
                    $('#showImageMd').addClass('d-none');
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
});

$(document).on("change", "#background_image_sm", function () {
    var filePath = $(this).val();
    var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;

    if (!allowedExtensions.exec(filePath)) {
        toastr.error('Invalid file type. Please upload a JPEG or PNG image.', 'Small Image', {
            "progressBar": false
        });
        $(this).val(null);
        $('#showImageSm').addClass('d-none');
        return false;
    } else {
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();
            $('#eImageSm').removeClass("d-none");
            reader.onload = function (event) {
                const img = new Image();

                img.onload = function () {
                    if (img.width >= 480 && img.height >= 432) {
                        $("#showImageSm").attr("src", event.target.result);
                        $('#showImageSm').removeClass('d-none');
                    } else {
                        toastr.error('Invalid image dimensions. The image size is less than 480 X 432 pixels.', 'Small Image', {
                            "progressBar": false
                        });
                        $(document).find('#background_image').val(null);
                        $('#showImageSm').attr('src', '');
                        $('#showImageSm').addClass('d-none');
                    }
                };
                img.onerror = function () {
                    toastr.error('Error loading image.', 'Small Image', {
                        "progressBar": false
                    });
                    $(document).find('#background_image').val(null);
                    $('#showImageSm').attr('src', '');
                    $('#showImageSm').addClass('d-none');
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
});


$(document).on("click", "button.del-button", function (e) {
    e.preventDefault();
    var length = $("#tableBody tr").length;
    if (length == 1) {
        toastr.error("Can't Delete, At least 1 address required.", "Error", {
            preventDuplicates: true,
            progressBar: true,
        });
        return false;
    } else {
        var rowId = $(this).data("row-id");
        $(`#${rowId}`).remove();
        count.val($(".tblrows").length);
    }
});

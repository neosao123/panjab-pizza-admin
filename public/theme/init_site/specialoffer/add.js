const baseUrl = document.getElementsByTagName("meta").baseurl.content;
const tblBody = $("#tbl-sides tbody");
const noofSides = $("input[name='noofSides']");
const count = $("input[name='rowCount']");
const inFrom = $("form[id='inform']");
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
function getMaxOfArray(numArray) { return Math.max.apply(null, numArray); }

function addRow() {
    var length = $('#tableBody tr').length;
    var arr = []; $("#tableBody tr").each(function () {
        arr.push(this.id.substring(3));
    });
    var rowCount = getMaxOfArray(arr);
    if (rowCount == "-Infinity") {
        rowCount = 0;
    } else { rowCount++; }

    if ($("#noofSides").val() == "") {
        toastr.error("Please enter number of sides", "Error", {
            preventDuplicates: true,
            progressBar: true,
        });
        return false;
    }
    if ($("#noofSides").val() == length) {
        toastr.error("Can't be added more than sides", "Error", {
            preventDuplicates: true,
            progressBar: true,
        });
        return false;
    }

    var html = `
        <tr id="row${rowCount}" class="tblrows" data-type="">
            <td>
                <select id="sides${rowCount}" name="sides[]" class="form-control select2 custom-select side" style="width:100%" onchange="checkDuplicateItem(${rowCount});" required data-parsley-required-message="Side is required."> 
                </select>                
            </td>
            <td>
               <select id="size${rowCount}" name="size[]" class="form-control select2 custom-select size" style="width:100%" required data-parsley-required-message="Size is required."> 
               </select> 
            </td>
            
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger del-button" data-row-id="row${rowCount}"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
    `;
    tblBody.append(html);
    count.val(rowCount);
    resetSidesDropDown(rowCount);
    inFrom.parsley("refresh");
}

function resetSidesDropDown(rowCount) {
    $(`select[id='sides${rowCount}']`).select2({
        placeholder: "Select",
        allowClear: true,
        ajax: {
            url: baseUrl + "/specialoffer/sides",
            type: "get",
            delay: 250,
            dataType: "json",
            data: function (params) {
                var query = {
                    search: params.term,
                    type: $("#type").val(),
                };
                return query;
            },
            processResults: function (response) {
                return {
                    results: $.map(response, function (item) {
                        return {
                            text: item.text,
                            id: item.id,
                            type: item.type
                        }
                    })
                };

            },
            cache: true,
        },
    }).on("select2:select", function (e) {
        var sideCode = $(e.currentTarget).val();
        getSize(sideCode, rowCount);
        var selectedOption = e.params.data;
        console.log(selectedOption);
        var type = e.params.data.type;
        console.log(type);
        $("#row" + rowCount).attr('data-type', type);
    });
}

function getSize(sideCode, rowCount) {
    $(`select[id='size${rowCount}']`).select2({
        placeholder: "Select",
        allowClear: true,
        ajax: {
            url: baseUrl + "/specialoffer/size",
            type: "get",
            delay: 250,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    sideCode: sideCode,
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
}

function checkDuplicateItem(id) {
    var currentElementValue = $("#sides" + id).val();
    if (currentElementValue !== undefined && currentElementValue !== null && currentElementValue !== "") {
        var currentElementId = $("#sides" + id).attr("id");
        $("select.sides").each(function (index, element) {
            var thisId = $(this).attr("id");
            var thisVal = $(this).val();
            if (thisId !== currentElementId) {
                if (thisVal !== "" && thisVal !== undefined && thisVal === currentElementValue) {
                    $(`#sides${id}`).val(null).trigger("change");
                    toastr.error("You cannot add similar sides more than once. Please select another.", "Duplication!");
                    return false;
                }
            }
        });
    }
}

function remove() {
    Swal.fire({
        title: "Are you sure?",
        text: "You want to change number of sides?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes",
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.close();
            $(".tblrows").remove();
            addRow();
        } else {
            Swal.close();
        }
    });
}

$(document).on("click", "button.add-sides", function (e) {
    e.preventDefault();
    addRow();
});

$(document).on("click", "button.del-button", function (e) {
    e.preventDefault();
    var rowId = $(this).data("row-id");
    $(`#${rowId}`).remove();
    count.val($(".tblrows").length);
});

$(document).ready(function () {
    resetSidesDropDown(0);
    $(document).on("change", "#file", function () {
        var filePath = $(this).val();
        var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;

        // Check File Type
        if (!allowedExtensions.exec(filePath)) {
            toastr.error('Invalid file type. Please upload a JPEG or PNG image.', 'Error', {
                "progressBar": false
            });
            $(this).val(null);
            return false;
        }

        const file = this.files[0];
        if (file) {
            let reader = new FileReader();

            reader.onload = function (event) {
                const img = new Image();
                img.onload = function () {
                    if (img.width >= 512 && img.height >= 512) {
                        $('#eImage').removeClass("d-none");
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
                img.onerror = function () {
                    toastr.error('Error reading the image file. Please try again.', 'Error', {
                        "progressBar": false
                    });
                    $("#file").val(null);
                };
                img.src = event.target.result;
            };

            reader.onerror = function () {
                toastr.error('Error reading the file. Please ensure the file is accessible.', 'Error', {
                    "progressBar": false
                });
            };

            reader.readAsDataURL(file);
        } else {
            toastr.error('No file selected. Please choose an image file.', 'Error', {
                "progressBar": false
            });
        }
    });




    $("#type").select2({
        placeholder: "Select",
        allowClear: true,
    }).on('select2:unselecting', function (e) {
        var data = e.params.args.data.id;
        Swal.fire({
            title: "Are you sure?",
            text: "You want to remove type?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.close();
                $('#tableBody tr[data-type=' + data + ']').remove();
            } else {
                Swal.close();
            }
        });

    });

    $("#pops").select2({
        placeholder: "Select",
        allowClear: true
    });

    $("#bottle").select2({
        placeholder: "Select",
        allowClear: true
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

    $(document).on("change blur", "#noofSides", function (e) {
        e.preventDefault();
        var rowCount = $('#tableBody tr').length;
        var noofside = $("#noofSides").val();
        if (noofside !== "" && noofside == 0) {
            $(".side").removeAttr("data-parsley-required-message");
            $(".side").removeAttr("required");
            $(".size").removeAttr("data-parsley-required-message");
            $(".size").removeAttr("required");
            $(".side").val("");
            $(".size").val("");
            $("#showSide").hide();
        } else {
            if (noofside < rowCount) {
                remove();
            }
            $("#showSide").show();
            $(".side").attr("required", true);
            $(".side").attr("data-parsley-required-message", "Side is required.");
            $(".size").attr("required", true);
            $(".size").attr("data-parsley-required-message", "Size is required.");
        }
        $("#inFrom").parsley().destroy();
        $("#inFrom").parsley();
    });

    $(document).on("change", "#price", function (e) {
        e.preventDefault();
        var currentValue = $(this).val();
        var extraValue = $("#extraLargePrice").val();
        if ((extraValue != "" && extraValue == 0) && currentValue == 0) {
            $("#showPriceError").text("Either the price of a Large pizza or the price of an Extra Large pizza must be greater than zero.");
            $("#submit").attr("disabled", true);
            setTimeout(function () {
                $("#showPriceError").text("");

            }, 3000);
            $(this).val("");
        } else {
            $("#submit").attr("disabled", false);
        }
    });

    $(document).on("change", "#limited_offer", function (e) {
        var start_date = $("#start_date").val();
        if (!$(this).is(":checked") && start_date !== "") {
            Swal.fire({
                title: "Remove limited offer?",
                text: "After this action, offer's start date and end date will be cleared",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes",
            }).then((result) => {
                if (result.isConfirmed) {

                } else {
                    Swal.close();
                }
            });
        }
    });

    $(document).on("change", "#extraLargePrice", function (e) {
        e.preventDefault();
        var currentValue = $(this).val();
        var largeValue = $("#price").val();
        if (currentValue == 0 && (largeValue != "" && largeValue == 0)) {
            $("#showextraLargePriceError").text("Either the price of a Large pizza or the price of an Extra Large pizza must be greater than zero.");
            $("#submit").attr("disabled", true);
            setTimeout(function () {
                $("#showextraLargePriceError").text("");

            }, 3000);
            $(this).val("");
        } else {
            $("#submit").attr("disabled", false);
        }
    });
});
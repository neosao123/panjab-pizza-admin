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
        url: baseUrl + '/users/deleteImage',
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
function checkPasswordMatch() {
	$("#CheckPasswordMatch").show();
    var password = $("#password").val();
    var confirmPassword = $("#password_confirmation").val();
    if (password != "" && confirmPassword != "") {
        if (password != confirmPassword) {
            $('#submit').prop('disabled', true);
            $("#CheckPasswordMatch").html("Passwords does not match!");
			setTimeout(() => {
				$("#CheckPasswordMatch").hide();
		    }, 5000);
        } else {
            $('#submit').prop('disabled', false);
            $("#CheckPasswordMatch").html("");
        }
    }
}
$(document).ready(function () {
     $("#storeLocation").select2({
			    placeholder: "Select",
                allowClear: true,
				ajax: {
					url:  baseUrl + "/storelocation/getStoreLocation",
					type: "get",
					delay:250,
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
    $("body").on("change", "#role", function(e) {
            var thisVal = $(this).find('option:selected').val();
            var thisVal = $(this).val();
            if (thisVal == "R_3") {
                $("#storeLoc").show();
                $("#storeLocation").attr("data-parsley-required", true);
                $("#storeLocation").attr("data-parsley-required-message", "Store location is required.");
            } else {
               $("#storeLoc").hide();
               $("#storeLocation").attr("data-parsley-required", false);
               $("#storeLocation").attr("data-parsley-required-message", "");
            }
        });
    $(document).on("change", "#file", function () {
        var filePath = $(this).val();
        var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;
        if (filePath != "") {
            if (!allowedExtensions.exec(filePath)) {
                toastr.error('Invalid File type', 'Users', {
                    "progressBar": false
                });
                $(this).val(null);
                return false;
            } else {
                const file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    $('#eDisImage').removeClass("d-none");
                    $('#eImage').addClass("d-none")
                    reader.onload = function (event) {
                        $("#showImage")
                            .attr("src", event.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    });
});
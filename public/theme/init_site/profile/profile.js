function isNumberKey(evt) {
    var charCode = evt.which ? evt.which : evt.keyCode;
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) return false;
    return true;
}
function ValidateAlpha(evt) {
    var keyCode = evt.which ? evt.which : evt.keyCode;
    if (keyCode > 47 && keyCode < 58) return false;
    return true;
}
$(document).ready(function () {
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

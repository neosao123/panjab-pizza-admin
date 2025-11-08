const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {

    $("#softdrink").select2({
        placeholder: "Select",
        allowClear: true,
        ajax: {
            url: baseUrl + "/getSoftDrink",
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


    $("#btnSearch").on("click", function (e) {
        var softdrink = $("#softdrink").val();
        getDataTable(softdrink);
    });

    $("#btnClear").click(function () {
        window.location.reload();
    });

    getDataTable("");
    function getDataTable(softdrink_p) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-SoftDrink")) {
            $("#dataTable-SoftDrink").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-SoftDrink").DataTable({
            stateSave: false,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getSoftDrinkList",
                type: "GET",
                data: {
                    softdrink: softdrink_p
                },
                complete: function (response) {

                },
            },
        });
    }

function operations() {
    $(document).on("click", ".delbtn", function (e) {
        e.preventDefault();
        var code = $(this).data("id");
        Swal.fire({
            title: "Are you sure?",
            text: "You want to delete this record",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: baseUrl + "/softdrinks/delete", // URL for soft drink delete
                    type: "get",
                    data: { code: code },
                    success: function (data) {
                        if (data.status === "success") {
                            Swal.fire({
                                icon: "success",
                                text: "record deleted successfully",
                            }).then(() => {
                                $("#dataTable-SoftDrink").DataTable().ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                text: "Failed to delete record",
                            });
                        }
                    },
                    error: function (xhr) {
                        var errorMsg = "Ajax request failed: " + xhr.responseText;
                        console.log("Ajax Request failed: " + errorMsg);
                    },
                });
            } else {
                Swal.fire({
                    icon: "info",
                    text: "Your record is safe",
                });
            }
        });
    });
}

operations();

});

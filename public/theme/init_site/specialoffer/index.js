const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {

    $("#specialoffer").select2({
        placeholder: "Select",
        allowClear: true,
        ajax: {
            url: baseUrl + "/getSpecialOffers",
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
        var specialoffer = $("#specialoffer").val();
        getDataTable(specialoffer);
    });

    $("#btnClear").click(function () {
        window.location.reload();
    });

    getDataTable("");
    function getDataTable(specialoffer_p) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-Specialoffer")) {
            $("#dataTable-Specialoffer").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-Specialoffer").DataTable({
            stateSave: false,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getSpecialoffersList",
                type: "GET",
                data: {
                    specialoffer:  specialoffer_p,
                },
                complete: function (response) {
                    operations();
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
                        url: baseUrl + "/specialoffer/delete",
                        type: "get",
                        data: {
                            code: code,
                        },
                        success: function (data) {
                            if (data) {
                                Swal.fire({
                                    icon: "success",
                                    text: "Your record is deleted",
                                }).then(() => {
                                    //getDataTable();
									window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: "success",
                                    text: "Your record is safe",
                                });
                            }
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            var errorMsg = "Ajax request failed: " + xhr.responseText;
                            console.log("Ajax Request for patient data failed : " + errorMsg);
                        },
                    });
                } else {
                    Swal.fire({
                        icon: "success",
                        text: "Your record is safe",
                    });
                }
            });
        });
    }
});

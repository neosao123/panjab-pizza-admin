const baseUrl = document.getElementsByTagName("meta").baseurl.content;

$(document).ready(function () {

    // Initialize DataTable and keep instance
    var table = loadDataTable();

    function loadDataTable() {
        $.fn.DataTable.ext.errMode = "none";

        if ($.fn.DataTable.isDataTable("#dataTable-Pictures")) {
            $("#dataTable-Pictures").DataTable().clear().destroy();
        }

        var table = $("#dataTable-Pictures").DataTable({
            stateSave: false,
            lengthMenu: [10, 25, 50, 100, 200, 500],
            processing: true,
            serverSide: true,
            ordering: true, 
            order: [[0, "desc"]], 
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getPictureList", 
                type: "GET"
            }
            ,
            columns: [
                { data: 0 }, // Sr No
                { data: 1 }, // Actions
                { data: 2 }, // Title
                { data: 5 }  // Status
            ]
        });
        return table;
    }

    // Delete functionality
    function operations(table) {
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
                        url: baseUrl + "/pictures/delete",
                        type: "get",
                        data: { code: code },
                        success: function (data) {
                            if (data.status === "success") {
                                Swal.fire({
                                    icon: "success",
                                    text: "Record deleted successfully",
                                }).then(() => {
                                    if (table) {
                                        table.ajax.reload(function () {
                                            var json = table.ajax.json();
                                            var total = json && json.recordsTotal ? json.recordsTotal : table.rows().count();
                                            var addBtn = $("a[href*='pictures/add']").first();
                                            if (addBtn.length) {
                                                if (total < 3) {
                                                    addBtn.show();
                                                } else {
                                                    addBtn.hide();
                                                }
                                            }
                                        }, false);
                                    } else {
                                        $("#dataTable-Pictures").DataTable().ajax.reload(null, false);
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    text: "Failed to delete record",
                                });
                            }
                        },
                        error: function (xhr) {
                            console.log("Ajax request failed: " + xhr.responseText);
                        }
                    });
                } else {
                    Swal.fire({ icon: "info", text: "Your record is safe" });
                }
            });
        });
    }

    operations(table); 

});

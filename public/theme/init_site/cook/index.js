const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
  $("#cook").select2({
    placeholder: "Select",
    allowClear: true,
    ajax: {
      url: baseUrl + "/getCook",
      type: "get",
      delay: 250,
      dataType: "json",
      data: function (params) {
        var query = {
          search: params.term,
        };
        return query;
      },
      processResults: function (response) {
        return {
          results: response,
        };
      },
      cache: true,
    },
  });

  $("#btnSearch").on("click", function (e) {
    var cook = $("#cook").val();
    getDataTable(cook);
  });

  $("#btnClear").click(function () {
    window.location.reload();
  });

  getDataTable("");
  function getDataTable(cook_p) {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dataTable-Cook")) {
      $("#dataTable-Cook").DataTable().clear().destroy();
    }
    var dataTable = $("#dataTable-Cook").DataTable({
      stateSave: false,
      lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
      processing: true,
      serverSide: true,
      ordering: false,
      searching: true,
      paging: true,
      ajax: {
        url: baseUrl + "/getCookList",
        type: "GET",
        data: {
          cook: cook_p,
        },
        complete: function (response) {},
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
                    url: baseUrl + "/cook/delete", // URL for cook
                    type: "get",
                    data: {
                        code: code,
                    },
                    success: function (data) {
                        if (data.status === "success") {
                            Swal.fire({
                                icon: "success",
                                text: "record deleted successfully",
                            }).then(() => {
                                $("#dataTable-Cook").DataTable().ajax.reload(null, false);
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                text: "Failed to delete record",
                            });
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
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

const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
  $("#storelocation").select2({
    placeholder: "Select",
    allowClear: true,
    ajax: {
      url: baseUrl + "/storelocation/getStoreLocation",
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
    var storelocation = $("#storelocation").val();
    getDataTable(storelocation);
  });

  $("#btnClear").click(function () {
    window.location.reload();
  });

  getDataTable("");
  function getDataTable(storelocation_p) {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dataTable-Storelocation")) {
      $("#dataTable-Storelocation").DataTable().clear().destroy();
    }
    var dataTable = $("#dataTable-Storelocation").DataTable({
      stateSave: false,
      lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
      processing: true,
      serverSide: true,
      ordering: false,
      searching: true,
      paging: true,
      ajax: {
        url: baseUrl + "/getStorelocationList",
        type: "GET",
        data: {
          storelocation: storelocation_p,
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
            url: baseUrl + "/storelocation/delete",
            type: "get",
            data: {
              code: code,
            },
            success: function (data) {
              if (data.status === 200) {
                Swal.fire({
                  icon: "success",
                  text: "Your record is deleted",
                }).then(() => {
                  getDataTable();
                  window.location.reload();
                });
              } else if (data.status === 300) {
                Swal.fire({
                  icon: "error",
                  text: data.message,
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

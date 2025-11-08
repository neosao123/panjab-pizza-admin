const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
  getDataTable("");
  function getDataTable() {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dataTable-dynamicSlider")) {
      $("#dataTable-dynamicSlider").DataTable().clear().destroy();
    }
    var dataTable = $("#dataTable-dynamicSlider").DataTable({
      stateSave: false,
      lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
      processing: true,
      serverSide: true,
      ordering: false,
      searching: true,
      paging: true,
      ajax: {
        url: baseUrl + "/getDynamicSliderList",
        type: "GET",
        data: {},
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
            url: baseUrl + "/dynamic-sliders/delete",
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

const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
  $("#toppings").select2({
    placeholder: "Select",
    allowClear: true,
    ajax: {
      url: baseUrl + "/getSidesToppings",
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
    var toppings = $("#toppings").val();
    getDataTable(toppings);
  });

  $("#btnClear").click(function () {
    window.location.reload();
  });

  getDataTable("");
  function getDataTable(toppings_p) {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dataTable-Sides-Toppings")) {
      $("#dataTable-Sides-Toppings").DataTable().clear().destroy();
    }
    var dataTable = $("#dataTable-Sides-Toppings").DataTable({
      stateSave: false,
      lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
      processing: true,
      serverSide: true,
      ordering: false,
      searching: true,
      paging: true,
      ajax: {
        url: baseUrl + "/getSidesToppingsList",
        type: "GET",
        data: {
          toppings: toppings_p,
        },
        complete: function (response) {},
      },
    });
  }
});

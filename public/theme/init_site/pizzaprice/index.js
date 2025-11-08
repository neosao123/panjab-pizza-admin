// Developer - Shreyas Mahamuni
// Working Date - 22-11-2023
// This Page for getDataTable

const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
  getDataTable("");
  function getDataTable() {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dataTable-pizzaprice")) {
      $("#dataTable-pizzaprice").DataTable().clear().destroy();
    }
    var dataTable = $("#dataTable-pizzaprice").DataTable({
      stateSave: false,
      lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
      processing: true,
      serverSide: true,
      ordering: false,
      searching: false,
      paging: true,
      ajax: {
        url: baseUrl + "/getPizzaPrice",
        type: "GET",
        data: {},
        complete: function (response) {},
      },
    });
  }
});

const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
  $("#btnSearch").on("click", function (e) {
    var fromDate = $("#fromDate").val();
    var toDate = $("#toDate").val();
    var deliveryType = $("#deliverytype").val();
    var orderfrom = $("#orderfrom").val();
    var orderno = $("#orderno").val();
    getDataTable(fromDate, toDate, deliveryType, orderfrom, orderno);
  });

  $("#btnClear").click(function () {
    window.location.reload();
  });
  $("#orderfrom").select2({
    placeholder: "Select",
    allowClear: true,
  });
  $("#deliverytype").select2({
    placeholder: "Select",
    allowClear: true,
  });

  $("#orderno").select2({
    placeholder: "Select",
    allowClear: true,
    ajax: {
      url: baseUrl + "/getOrders",
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

  getDataTable("", "");
  function getDataTable(
    fromDate_p,
    toDate_p,
    deliveryType_p,
    orderfrom_p,
    orderno_p
  ) {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dataTable-Reports")) {
      $("#dataTable-Reports").DataTable().clear().destroy();
    }

    jQuery.fn.DataTable.Api.register(
      "buttons.exportData()",
      function (options) {
        if (this.context.length) {
          var jsonResult = $.ajax({
            url: baseUrl + "/getReportsList",
            data: {
              export: 1,
              fromDate: fromDate_p,
              toDate: toDate_p,
              deliveryType: deliveryType_p,
              orderfrom: orderfrom_p,
              orderno: orderno_p,
            },
            type: "GET",
            success: function (result) {},
            async: false,
          });
          var jencode = JSON.parse(jsonResult.responseText);
          return {
            body: jencode.data,
            header: $("#reports thead tr th")
              .map(function () {
                return this.innerHTML;
              })
              .get(),
          };
        }
      }
    );

    var dataTable = $("#dataTable-Reports").DataTable({
      dom: 'B<"flex-wrap mt-2"fl>trip',
      buttons: [
        {
          extend: "excel",
          title: "Reports",
        },
      ],
      stateSave: false,
      lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
      processing: true,
      serverSide: true,
      ordering: false,
      searching: true,
      paging: true,
      ajax: {
        data: {
          export: 0,
          fromDate: fromDate_p,
          toDate: toDate_p,
          deliveryType: deliveryType_p,
          orderfrom: orderfrom_p,
          orderno: orderno_p,
        },
        url: baseUrl + "/getReportsList",
        type: "GET",
        complete: function (response) {},
      },
    });
  }
});

const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
  $("#btnSearch").on("click", function (e) {
    var fromDate = $("#fromDate").val();
    var toDate = $("#toDate").val();
    var deliveryType = $("#deliverytype").val();
    var orderfrom = $("#orderfrom").val();
    var orderno = $("#orderno").val();
    var storeLocation = $("#storeLocation").val();
    var storeLocationName = $("#storeLocation").find(":selected").text();
    if (storeLocation !== "") {
      getDataTable(
        fromDate,
        toDate,
        deliveryType,
        orderfrom,
        orderno,
        storeLocation,
        storeLocationName
      );
    } else {
      Swal.fire({
        icon: "error",
        text: "Store Location is required",
      });
    }
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

  //   getDataTable("", "");
  function getDataTable(
    fromDate_p,
    toDate_p,
    deliveryType_p,
    orderfrom_p,
    orderno_p,
    storelocation_p,
    storeLocationName_p
  ) {
    $.fn.DataTable.ext.errMode = "none";
    if ($.fn.DataTable.isDataTable("#dataTable-Reports")) {
      $("#dataTable-Reports").DataTable().clear().destroy();
    }

    function addCustomRows() {
      // You can create custom rows or fetch data to add to the DataTable here.
      var customRows = [
        ["Custom Data 1", "Custom Data 2", "Custom Data 3"],
        ["More Custom Data 1", "More Custom Data 2", "More Custom Data 3"],
      ];

      dataTable.rows.add(customRows).draw();
    }

    jQuery.fn.DataTable.Api.register(
      "buttons.exportData()",
      function (options) {
        if (this.context.length) {
          var jsonResult = $.ajax({
            url: baseUrl + "/getReportsListByStoreLocation",
            data: {
              export: 1,
              fromDate: fromDate_p,
              toDate: toDate_p,
              deliveryType: deliveryType_p,
              orderfrom: orderfrom_p,
              orderno: orderno_p,
              storeLocation: storelocation_p,
            },
            type: "GET",
            success: function (result) {},
            async: false,
          });
          var jencode = JSON.parse(jsonResult.responseText);
          return {
            body: jencode.data,
            header: $("#store-reports thead tr th")
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
          title: "Reports By Store Location",
          message: storeLocationName_p,
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
          storeLocation: storelocation_p,
        },
        url: baseUrl + "/getReportsListByStoreLocation",
        type: "GET",
        complete: function (response) {},
      },
    });
  }
});

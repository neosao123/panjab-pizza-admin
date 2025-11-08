const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
    $("#btnSearch").on("click", function (e) {
        var filterDate = $("#filter_date").val();
        var storeLocation = $("#storeLocation").val();

        getDataTable(
            filterDate,
            storeLocation
        );

    });

    $("#btnClear").click(function () {
        window.location.reload();
    });

    var filterDate = $("#filter_date").val();

    getDataTable(filterDate, "");

    function getDataTable(
        date_p,
        store_p
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
                        url: baseUrl + "/reports/store-summary/data",
                        data: {
                            export: 1,
                            filter_date: date_p,
                            filter_store: store_p,
                        },
                        type: "GET",
                        success: function (result) { },
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

        $("#dataTable-Reports").DataTable({
            dom: 'B<"flex-wrap mt-2"fl>trip',
            buttons: [
                {
                    extend: "excel",
                    title: "Stores Summary",
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
                    filter_date: date_p,
                    filter_store: store_p,
                },
                url: baseUrl + "/reports/store-summary/data",
                type: "GET",
                complete: function (response) { },
            },
        });
    }
});

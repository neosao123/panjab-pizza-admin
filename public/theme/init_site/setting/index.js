const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
    getDataTable();
    function getDataTable() {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-setting")) {
            $("#dataTable-setting").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-setting").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getSettingList",
                type: "GET",
                complete: function (response) {
                    operations();
                },
            },
        });
    }

    function operations() {
    }
});

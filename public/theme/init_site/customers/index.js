const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
    $("#customercode").select2({
        placeholder: "Select",
        allowClear: true,
        ajax: {
            url: baseUrl + "/customers/getCustomer",
            type: "get",
            delay: 250,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
	
	$("#email").select2({
        placeholder: "Select",
        allowClear: true,
        ajax: {
            url: baseUrl + "/customers/getEmail",
            type: "get",
            delay: 250,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
	
	$("#mobile").select2({
        placeholder: "Select",
        allowClear: true,
        ajax: {
            url: baseUrl + "/customers/getMobile",
            type: "get",
            delay: 250,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        }
    });
	
	
	$("#btnSearch").on("click", function (e) {
		var customercode=$("#customercode").val();
		var email = $("#email").val();
		var mobile = $("#mobile").val();
        getDataTable(customercode,email,mobile);
    });

    $("#btnClear").click(function () {
			window.location.reload();
    });
	
    getDataTable("","","","");
    function getDataTable(customercode,email,mobile) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-Customer")) {
            $("#dataTable-Customer").DataTable().clear().destroy();
        }
		jQuery.fn.DataTable.Api.register( 'buttons.exportData()', function ( options ) {
			if ( this.context.length ) {
				var jsonResult = $.ajax({
					url: baseUrl + "/getCustomerList",
					data: {
						 'export':1,
						 'customercode':customercode,
						 'email':email,
						 'mobile':mobile,
					},
					type:"GET", 
					success: function (result) { 
					},
					async: false
				});
				var jencode=JSON.parse(jsonResult.responseText);
				return {body: jencode.data, header: $("#customerReport thead tr th").map(function() { return this.innerHTML; }).get()};
			}
		});
        var dataTable = $("#dataTable-Customer").DataTable({
			/*dom:'B<"flex-wrap mt-2"fl>trip',
		    buttons: [{
				extend: 'excel',
				title:'Customer'
			}],*/
            stateSave: false,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: true,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getCustomerList",
                type: "GET",
				 data: {
					 'export':0,
					 customercode:customercode,
					 email:email,
					 mobile:mobile,
                },
                complete: function (response) {
                    //operations();
                },
            },
        });
    }
});

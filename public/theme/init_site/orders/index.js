const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {

    $("#orders").select2({
        placeholder: "Select",
        allowClear: true,
        ajax: {
            url: baseUrl + "/getOrders",
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
	
	$("#orderfrom").select2({
        placeholder: "Select",
        allowClear: true,
	});

    $("#btnSearch").on("click", function (e) {
         var orders = $("#orders").val();
		 var orderfrom = $("#orderfrom").val();
		 var orderStatus=$("#orderSta").val();
        getDataTable(orders,orderfrom,orderStatus);
    });

    $("#btnClear").click(function () {
        window.location.reload();
    });

    getDataTable("","");
    function getDataTable(orders_p,orderfrom_p,orderstatus_p) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-Orders")) {
            $("#dataTable-Orders").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-Orders").DataTable({
            stateSave: false,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getOrdersList",
                type: "GET",
                data: {
                    orders: orders_p,
					orderfrom:orderfrom_p,
					orderstatus:orderstatus_p
                },
                complete: function (response) {

                },
            },
        });
    }
	
	$(document).on("change", "#orderStatus", function (e) {
        e.preventDefault();
		var orderStatus=$(this).val();
		var orderCode=$("#orderCode").val();
		if(orderStatus!="delivered"){
		Swal.fire({
                title: "Are you sure?",
                text: "You want to change status of order.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes",

            }).then((result) => {
            if (result.isConfirmed) {
			$.ajax({
			url:baseUrl + "/orders/updateStatus",
			method:"GET",
			data:{orderStatus:orderStatus,orderCode:orderCode},
			datatype:"text",
			success: function(data)
				{
				   if(data.status=="success"){
					    Swal.fire({
                                icon: "success",
                                text: "Order status changed successfully.",
                         }).then(() => {
                                window.location.href = baseUrl + "/orders/view/"+orderCode;
                        });
				   }else{
					    Swal.fire({
                                icon: "error",
                                text: "Failed to update status of order.",
                         }).then(() => {
                                window.location.href = baseUrl + "/orders/view/"+orderCode;
                        });
				   }
				}
		      });		  
		   } else {
				Swal.fire({
					icon: "success",
					text: "Your record is safe",
				});

		   }
           });		   
		}		   
	 });


});

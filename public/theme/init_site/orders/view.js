const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
	$(document).on("change", "#orderStatus", function (e) {
        e.preventDefault();
		var orderStatus=$(this).val();
		var orderCode=$("#orderCode").val();
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
			url:baseUrl + "/orders/updateOrderStatus",
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
                                text: data.message,
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
	 });


});

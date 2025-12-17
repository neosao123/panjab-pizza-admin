const baseUrl = document.getElementsByTagName("meta").baseurl.content;
let currentStatus = $("#orderStatus").val();

$(document).on("mousedown", "#orderStatus", function () {
    currentStatus = this.value;
});

$(document).on("change", "#orderStatus", function () {
    let newStatus = $(this).val();
    let orderCode = $("#orderCode").val();

    Swal.fire({
        title: "Are you sure?",
        text: "You want to change status of order.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "No"
    }).then((result) => {

        if (result.isConfirmed) {
            $.ajax({
                url: baseUrl + "/orders/updateOrderStatus",
                method: "GET",
                data: {
                    orderStatus: newStatus,
                    orderCode: orderCode
                },
                success: function (data) {
                    if (data.status === "success") {
                        Swal.fire("Success", "Order status changed successfully.", "success")
                            .then(() => {
                                window.location.reload();
                            });
                    } else {
                        $("#orderStatus").val(currentStatus);
                        Swal.fire("Error", data.message, "error");
                    }
                }
            });
        } else {
            $("#orderStatus").val(currentStatus);
        }
    });
});

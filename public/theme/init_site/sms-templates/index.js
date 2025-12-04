const baseUrl = document.getElementsByTagName("meta").baseurl.content;

$(document).ready(function () {
    'use strict';

    // Initialize DataTable
    $('#dataTable-sms-templates').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseUrl + '/getSmsTemplateList',
            type: 'GET'
        },
        columns: [
            { data: 0 }, // Sr. No.
            { data: 1 }, // Action
            { data: 2 }, // Title
            { data: 3 }, // Template
            { data: 4 }  // Status
        ],
        columnDefs: [
            {
                targets: [1, 4], // Action and Status columns
                orderable: false
            }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search records"
        }
    });

    // Delete SMS Template
    $(document).on('click', '.delete-template', function (e) {
        e.preventDefault();

        let id = $(this).data('id');

        Swal.fire({
            title: "Are you sure?",
            text: "This will delete the SMS template permanently!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: baseUrl + "/sms-templates/delete/" + id,
                    type: "GET",
                    success: function (response) {
                        if (response.status === 200) {
                            Swal.fire(
                                "Deleted!",
                                response.message,
                                "success"
                            );
                            $("#dataTable-sms-templates").DataTable().ajax.reload();
                        } else {
                            Swal.fire(
                                "Error!",
                                response.message,
                                "error"
                            );
                        }
                    },
                    error: function (xhr) {
                        let message = "Failed to delete SMS template.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire(
                            "Error!",
                            message,
                            "error"
                        );
                    }
                });
            }
        });
    });
});

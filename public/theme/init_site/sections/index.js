const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
    'use strict';

    // Initialize DataTable
    $('#dataTable-sections').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseUrl + '/getSectionList',
            type: 'GET'
        },
        columns: [
            { data: 0 }, // Sr. No.
            { data: 1 }, // Action
            { data: 2 }, // Title
            { data: 3 }, // Sub Title
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


    $(document).on('click', '.delete-section', function (e) {
        e.preventDefault();

        let id = $(this).data('id');

        Swal.fire({
            title: "Are you sure?",
            text: "This will delete the section permanently!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: baseUrl + "/sections/delete/" + id,
                    type: "GET",
                    success: function (response) {
                        Swal.fire(
                            "Deleted!",
                            "Section deleted successfully.",
                            "success"
                        );

                        $("#dataTable-sections").DataTable().ajax.reload();
                    },
                    error: function () {
                        Swal.fire(
                            "Error!",
                            "Failed to delete section.",
                            "error"
                        );
                    }
                });

            }
        });
    });

});
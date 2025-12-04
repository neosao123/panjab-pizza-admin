const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {

    // 🔥 Select2 with AJAX, Limit/Offset, Exclude IDs
    let excludeIds = [1, 2, 3, 4,6]; // IDs to exclude


    $('#dataTable-sms-logs').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: baseUrl + '/customers/sms-logs/list',
            type: 'GET'
        },
        columns: [
            { data: 0 }, // Sr No
            { data: 1 }, // Template
            { data: 2 }, // Mobile
            { data: 3 }
        ],
        columnDefs: [
            { targets: [3], orderable: false }
        ],
        order: [[0, "desc"]],
        pageLength: 5,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search logs"
        }
    });


    $('#template').select2({
        ajax: {

            url: baseUrl + '/customers/templates/get',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term,
                    limit: 10,
                    offset: ((params.page || 1) - 1) * 10,
                    exclude_ids: excludeIds // Exclude template IDs 1,2,3,4
                };
            },
            processResults: function (response, params) {
                params.page = params.page || 1;

                return {
                    results: response.data.map(t => ({
                        id: t.id,
                        text: t.title,
                        message: t.template
                    })),
                    pagination: {
                        more: (params.page * 10) < response.total
                    }
                };
            },
            cache: true
        },
        placeholder: '-- Select Template --',
        minimumInputLength: 0
    });

    // 🔥 Template Preview on Select
    $('#template').on('select2:select', function (e) {
        let data = e.params.data;
        let message = data.message || '';

        $('#templatePreview').html(message);
        updateMessageStats(message);
    });

    // Update Message Length & SMS Parts
    function updateMessageStats(msg) {
        let len = msg.length;
        $('#messageLength').text(len);
        $('#messageCount').text(Math.ceil(len / 160));
    }

    // 🔥 Confirm before sending
    $('#smsForm').on('submit', function (e) {
        e.preventDefault();
        let form = this;
        let formData = $(form).serialize();
        let $sendBtn = $('#sendBtn');

        // Disable button & show loading
        $sendBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Validating...');

        // 🔥 AJAX Validation Request
        $.ajax({
             url: baseUrl + '/customers/validate-sms',

            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // ✅ Validation passed - Show confirmation
                    Swal.fire({
                        title: 'Send SMS to All Customers?',
                        html: '<p>This will queue SMS for <strong>' + response
                            .customer_count + '</strong> customers.</p>',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, Queue SMS!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Submit form
                            form.submit();
                        } else {
                            // Reset button
                            $sendBtn.prop('disabled', false).html(
                                '<i class="fa fa-paper-plane"></i> Send SMS to All Customers'
                            );
                        }
                    });
                }
            },
            error: function (xhr) {
                // ❌ Validation failed
                $sendBtn.prop('disabled', false).html(
                    '<i class="fa fa-paper-plane"></i> Send SMS to All Customers');

                let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr
                    .responseJSON.errors : {};
                let errorMessage = '';

                if (Object.keys(errors).length > 0) {
                    errorMessage = '<ul style="text-align: left;">';
                    $.each(errors, function (key, value) {
                        errorMessage += '<li>' + value[0] + '</li>';
                    });
                    errorMessage += '</ul>';
                } else {
                    errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr
                        .responseJSON.message : 'Validation failed!';
                }

                Swal.fire({
                    title: 'Validation Error',
                    html: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            }
        });
    });
});

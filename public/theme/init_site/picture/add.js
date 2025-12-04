$(document).ready(function () {
    'use strict';

    // Initialize Select2
    $('.select2').select2({
        placeholder: 'Select Product',
        allowClear: true
    });

    // Image drag and drop functionality
    let dropArea = $("#drop-area");
    let fileInput = $("#fileInput");
    let preview = $("#preview");

    dropArea.on("click", function (e) {
        fileInput[0].click();
    });

    fileInput.on("change", function (e) {
        handleFiles(e.target.files);
    });

    dropArea.on("dragover", function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropArea.addClass("dragover");
    });

    dropArea.on("dragleave", function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropArea.removeClass("dragover");
    });

    dropArea.on("drop", function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropArea.removeClass("dragover");
        let files = e.originalEvent.dataTransfer.files;
        fileInput[0].files = files;
        handleFiles(files);
    });

    function handleFiles(files) {
        if (files.length > 0) {
            let file = files[0];
            if (file.type.startsWith("image/")) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    preview.attr("src", e.target.result).show();
                };
                reader.readAsDataURL(file);
            }
        }
    }

    // Pizza type change event
    $('#pizza_type').on('change', function() {
        let pizzaType = $(this).val();
        let productSelect = $('#product_id');

        // Reset product dropdown
        productSelect.html('<option value="">Loading...</option>');
        productSelect.prop('disabled', true);

        if (pizzaType) {
            // Fetch products based on pizza type
            $.ajax({
                url: '/pictures/get-products',
                type: 'GET',
                data: {
                    category_type: pizzaType
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let options = '<option value="">Select Product</option>';

                        response.data.forEach(function(product) {
                            options += `<option value="${product.code}">${product.name}</option>`;
                        });

                        productSelect.html(options);
                        productSelect.prop('disabled', false);

                        // Reinitialize Select2
                        productSelect.select2({
                            placeholder: 'Select Product',
                            allowClear: true
                        });
                    } else {
                        productSelect.html('<option value="">No products found</option>');
                        productSelect.prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching products:', error);
                    productSelect.html('<option value="">Error loading products</option>');
                    productSelect.prop('disabled', true);
                }
            });
        } else {
            productSelect.html('<option value="">Select Pizza Type First</option>');
            productSelect.prop('disabled', true);
        }
    });
});

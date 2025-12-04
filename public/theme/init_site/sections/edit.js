const baseUrl = document.getElementsByTagName("meta").baseurl.content;

$(document).ready(function () {
    'use strict';

    // Initialize form validation
    $('#inFrom').parsley();

    // Add new line entry
    $('#addLineEntry').on('click', function () {
        var newRow = `
            <div class="line-entry-row" id="lineRow${lineEntryCount}" data-index="${lineEntryCount}">
                <input type="hidden" name="line_id[]" value="##NA" />
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Image: <span style="color:red">*</span></label>
                        <input type="file" name="line_image[]" accept=".jpg, .png, .jpeg"
                          class="form-control line-image" data-parsley-fileextension="jpg,png,jpeg" 
                          data-parsley-trigger="change" data-parsley-trigger="change" required
                          data-parsley-required-message="Image is required">
                        <div class="mt-2 d-none image-preview" id="preview${lineEntryCount}">
                          <img class="img-thumbnail" width="100" height="100" 
                            style="width: 150px; height:100px" src="">
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Title: <span style="color:red">*</span></label>
                        <input type="text" name="line_title[]" class="form-control" required
                          data-parsley-required-message="Title is required" 
                          data-parsley-minlength="2" 
                          data-parsley-trigger="change">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Counter: <span style="color:red">*</span></label>
                        <input type="text" name="counter[]" class="form-control" 
                          required
                          data-parsley-required-message="Counter is required">
                    </div>
                    <div class="col-md-1 form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-block remove-line" 
                          data-row-id="lineRow${lineEntryCount}">
                          <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        $('#lineEntriesContainer').append(newRow);
        lineEntryCount++;

        // Reinitialize parsley for new elements
        $('#inFrom').parsley().refresh();
    });

    // Remove line entry
    $(document).on('click', '.remove-line', function () {
        var rowId = $(this).data('row-id');
        var id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this line entry?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // If it's an existing record (has code), delete from server
                if (id && id !== '##NA') {
                    $.ajax({
                        url: baseUrl + '/sections/deleteLineentries/' + id,
                        type: 'GET',
                        success: function (response) {
                            $('#' + rowId).remove();
                            Swal.fire(
                                'Deleted!',
                                'Line entry has been deleted.',
                                'success'
                            );
                        },
                        error: function () {
                            Swal.fire(
                                'Error!',
                                'Failed to delete line entry.',
                                'error'
                            );
                        }
                    });
                } else {
                    // Just remove from DOM if it's a new entry
                    $('#' + rowId).remove();
                    Swal.fire(
                        'Deleted!',
                        'Line entry has been removed.',
                        'success'
                    );
                }
            }
        });
    });

    // Delete image
    $(document).on('click', '.delete-image', function () {
        var id = $(this).data('id');
        var image = $(this).data('image');
        var index = $(this).data('index');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to delete this image?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: baseUrl + '/sections/deleteImage',
                    type: 'GET',
                    data: {
                        id: id,
                        value: image
                    },
                    success: function (response) {
                        if (response == 'true' || response == true) {
                            $('#existingImage' + index).remove();

                            // find related file input for image
                            let fileInput = $(`#lineRow${index} input[name="line_image[]"]`);

                            // add required attribute
                            fileInput.attr("required", true);

                            // add parsley required message (optional)
                            fileInput.attr("data-parsley-required-message", "Image is required");

                            // refresh parsley validation
                            $('#inFrom').parsley().reset();
                            fileInput.parsley().reset();
                            
                            Swal.fire(
                                'Deleted!',
                                'Image has been deleted.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete image.',
                                'error'
                            );
                        }
                    },
                    error: function () {
                        Swal.fire(
                            'Error!',
                            'Failed to delete image.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Image preview
    // Image preview
    $(document).on('change', '.line-image', function () {
        var input = this;
        var row = $(this).closest('.line-entry-row');
        var index = row.data('index');
        var preview = $('#preview' + index);
        var existing = $('#existingImage' + index);

        // Hide old image when user selects new one
        if (existing.length > 0) {
            existing.addClass('d-none');
        }

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                preview.removeClass('d-none');
                preview.find('img').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    });


    // Form submit validation
    $('#inFrom').on('submit', function (e) {
        var lineEntries = $('.line-entry-row').length;

        if (lineEntries === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please add at least one line entry!',
            });
            return false;
        }
    });

    // Custom file extension validator for Parsley
    window.Parsley.addValidator('fileextension', {
        requirementType: 'string',
        validateString: function (value, requirement, parsleyInstance) {
            var file = parsleyInstance.$element[0].files;
            if (file.length == 0) {
                return true;
            }
            var ext = file[0].name.split('.').pop();
            var required = requirement.split(',');
            if (required.indexOf(ext.toLowerCase()) == -1) {
                return false;
            }
            return true;
        },
        messages: {
            en: 'The file must be of type: %s'
        }
    });
});
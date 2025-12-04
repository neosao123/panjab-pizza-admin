$(document).ready(function () {
    'use strict';

    // Initialize form validation
    $('#inFrom').parsley();

    // Character counter for template
    function updateCharCount() {
        var charCount = $('#template').val().length;
        $('#charCount').text(charCount);
    }

    // Update character count on load
    updateCharCount();

    // Update character count on input
    $('#template').on('input', function () {
        updateCharCount();
    });

    // Form submit validation
    $('#inFrom').on('submit', function (e) {
        var title = $('#title').val().trim();
        var template = $('#template').val().trim();

        if (title === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please enter a title!',
            });
            return false;
        }

        if (template === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please enter a template!',
            });
            return false;
        }

        if (template.length < 10) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Template must be at least 10 characters long!',
            });
            return false;
        }
    });

    // Title case conversion
    $('#title').on('blur', function () {
        var title = $(this).val();
        $(this).val(title.toLowerCase().replace(/\b\w/g, l => l.toUpperCase()));
    });
});

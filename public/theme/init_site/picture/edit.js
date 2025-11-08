const baseUrl = $('meta[name="baseurl"]').attr('content');

function isNumberKey(evt) {
    const charCode = evt.which ? evt.which : evt.keyCode;
    return (charCode == 46 || charCode <= 31 || (charCode >= 48 && charCode <= 57));
}

function ValidateAlpha(evt) {
    const charCode = evt.which ? evt.which : window.event.keyCode;
    if (charCode <= 13) return true;
    const keyChar = String.fromCharCode(charCode);
    return /^[a-zA-Z ]+$/.test(keyChar);
}

function deleteImage(code, value) {
    $.ajax({
        url: `${baseUrl}/pictures/deleteImage`,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            code: code,
            value: value
        },
        success: function (data) {
            if (data === true || data === "true") {
                // Clear file input in a robust way so browsers will detect new selections
                const fileEl = document.getElementById('file');
                if (fileEl) {
                    try {
                        fileEl.value = '';
                    } catch (e) {
                        $('#file').val(null);
                    }
                }
                $('#showImage').attr('src', '').addClass('d-none');
                $('#image-block button.btn-danger').hide();
            }
        }
    });
}

$(document).ready(function () {
    const dropArea = $("#image-block");
    const fileInput = $("#file");
    const previewImg = $("#showImage");

    // --- Initialize existing image ---
    if (previewImg.attr("src")) {
        previewImg.removeClass('d-none'); 
    }

    // File input change
    fileInput.on("change", function () {
        if (this.files.length > 0) {
            handleFile(this.files[0]);
        }
    });

    // Click to open file picker
    dropArea.on("click", function (e) {
        if ($(e.target).is("#image-block, p, #showImage")) {
            fileInput[0].click();
        }
    });

    // Drag & Drop
    dropArea.on("dragover dragenter", function (e) {
        e.preventDefault();
        dropArea.addClass("dragover");
    });

    dropArea.on("dragleave dragend drop", function (e) {
        e.preventDefault();
        dropArea.removeClass("dragover");
    });

    dropArea.on("drop", function (e) {
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) handleFile(files[0]);
    });

    function handleFile(file) {
        const allowedExtensions = /\.(jpeg|jpg|png|webp)$/i;
        if (!allowedExtensions.test(file.name)) {
            // Show inline validation message
            $('#imageError').text('Invalid file type. Allowed: .jpg, .jpeg, .png, .webp');
            fileInput.val(null);
            previewImg.attr("src", '').addClass('d-none');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            const img = new Image();
            img.onload = function () {
                // Require exact 512x512 to match server-side validation
                if (img.width >= 512 && img.height >= 512) {
                    previewImg.attr("src", event.target.result).removeClass('d-none');
                } else {
                    // Show inline validation message for dimensions
                    $('#imageError').text('The image must be exactly 512x512 pixels.');
                    try {
                        fileInput[0].value = '';
                    } catch (e) {
                        fileInput.val(null);
                    }
                    previewImg.attr("src", '').addClass('d-none');
                }
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    // Clear inline error when user focuses or clicks the area to choose a new file
    dropArea.on('click', function () {
        $('#imageError').text('');
    });
});


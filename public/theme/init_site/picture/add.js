$(document).ready(function () {
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
});

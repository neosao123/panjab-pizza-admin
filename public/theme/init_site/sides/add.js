document.addEventListener('DOMContentLoaded', function () {
    let tblBody = document.getElementById('tableBody');
    let rowCount = tblBody.querySelectorAll('tr').length; // start from existing rows

    // Helper function to get all current sizes in lowercase
    function getExistingSizes() {
        return Array.from(tblBody.querySelectorAll('input[name="size[]"]'))
            .map(input => input.value.trim().toLowerCase())
            .filter(val => val !== '');
    }

    // Add new row
    document.querySelector('.add-sides').addEventListener('click', function (e) {
        e.preventDefault();

        const newRowId = 'row' + rowCount;
        const newRow = document.createElement('tr');
        newRow.id = newRowId;

        newRow.innerHTML = `
            <td>
                <input type="text" class="form-control size-input" name="size[]" required placeholder="Size">
                <div class="invalid-feedback" style="display:none;">Each size must be unique</div>
            </td>
            <td>
                <input type="number" step="0.01" min="0" class="form-control" name="price[]" required placeholder="Price">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger del-button" data-row-id="${newRowId}">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        `;

        tblBody.appendChild(newRow);
        rowCount++;
    });

    // Delete row
    tblBody.addEventListener('click', function (e) {
        const btn = e.target.closest('.del-button');
        if (btn) {
            const rowId = btn.dataset.rowId;
            const row = document.getElementById(rowId);
            if (row) row.remove();
        }
    });

    // Validate duplicates on form submit
    const form = document.querySelector('form'); // make sure your form has <form> tag
    form.addEventListener('submit', function (e) {
        let hasDuplicate = false;
        const inputs = tblBody.querySelectorAll('input[name="size[]"]');
        const allValues = Array.from(inputs).map(input => input.value.trim().toLowerCase());

        // Reset previous invalid states
        inputs.forEach(input => {
            input.classList.remove('is-invalid');
            const feedback = input.nextElementSibling;
            if (feedback) feedback.style.display = 'none';
        });

        // Check for duplicates
        inputs.forEach(input => {
            const value = input.value.trim().toLowerCase();
            if (value && allValues.filter(v => v === value).length > 1) {
                input.classList.add('is-invalid');
                const feedback = input.nextElementSibling;
                if (feedback) feedback.style.display = 'block';
                hasDuplicate = true;
            }
        });

        if (hasDuplicate) {
            e.preventDefault(); // prevent form submission if duplicates exist
        }
    });  
$(document).ready(function () {

    const typeColors = {
        "Side":   { bg: "#ffe6e6", color: "#990000" },
        "Subs":   { bg: "#e6f7ff", color: "#004466" },
        "Poutine":{ bg: "#fff0cc", color: "#664400" },
        "Plant Bites": { bg: "#e6ffe6", color: "#006600" },
        "Tenders": { bg: "#fff0f5", color: "#660066" }
    };

    $("#type").select2({
        placeholder: "Select Type",
        allowClear: true,
        templateResult: function(data) {
            if (!data.id) return data.text; // placeholder

            var colors = typeColors[data.text];
            if (colors && data.element) {
                // Apply only background & text color
                $(data.element).css({
                    "background-color": colors.bg,
                    "color": colors.color
                });
                return data.text; // return text only, keeps layout intact
            }
            return data.text;
        },
        templateSelection: function(data) {
            return data.text;
        }
    });

});
    

});

const baseUrl = document.getElementsByTagName("meta").baseurl.content;
function checkAll(id) {
    if ($('#allcheck' + id).is(":checked")) {
        $('.cb-element' + id).prop('checked', true);
    } else {
        $('.cb-element' + id).prop('checked', false);
    }
}
function checkAllSubcheck(submenus, id, startpt) {
    var start = Number(startpt) + Number(1)
    var end = Number(startpt) + Number(submenus)
    if ($('#allsubcheck' + id).is(":checked")) {
        for (i = start; i <= end; i++) {
            $('#allcheck' + i).prop("checked", true)
            checkAll(i)
        }
    } else {
        for (i = start; i <= end; i++) {
            $('#allcheck' + i).prop("checked", false)
            checkAll(i)
        }
    }
}
function validateAllCheck(id, submenus) {
    var checkedLength = $('.cb-element' + id + ':checked').length;
    if (checkedLength == 0) {
        $('#allcheck' + id).prop('checked', false);
    } else if (checkedLength == 4) {
        $('#allcheck' + id).prop('checked', true);
    }
}

function clearSelection() {
    window.location.reload();
}

function getMenuList() {
    var role = $('#role').val();
    if (role != '') {
        $('#btnClear').removeClass('d-none');
        $.ajax({
            type: 'get',
            url: baseUrl + "/rolewiserights/getMenuList/" + role,
            data: {
                role: role,
            },
            beforeSend: function () {
                $('#btnSearch').prop('disabled', true);
                $('#btnSearch').text('Please wait..');
            },
            success: function (response) {
                $('#btnSearch').prop('disabled', false);
                $('#btnSearch').text('Search');
                var obj = JSON.parse(response);
                $('#role').prop('disabled', false);
                $('#btnSearch').removeClass('d-none');
                if (obj.status) {
                    $('#rightsDiv').removeClass('d-none');
                    $('#menuHtml').html(obj.menuHtml);
                    $('#role').prop('disabled', true);
                    $('#btnSearch').addClass('d-none');
                    $('#menuHtml').html(obj.menuHtml);
                } else {
                    $('#menuHtml').html('');
                    $('#rightsDiv').addClass('d-none');
                    toastr.success('Failed to get menulist', 'Role wise rights', { "progressBar": true });
                }
            }
        })
    } else {
        $('#btnClear').addClass('d-none');
        toastr.error('Please select role first', 'Role wise rights', { "progressBar": true });
        $('#role').focus();
        return false;
    }
}
function updateMenuRights() {
    var table = document.getElementById("rights_table");
    var table_len = (table.rows.length) - 1;
    var tr = table.getElementsByTagName("tr");
    var role = $('#role').val();
    var roleArr = [];
    if (role != '') {
        for (i = 1; i <= table_len; i++) {
            var menuArr = {};
            var id = tr[i].id.substring(3);
            var menu = $('#menu' + id).val();
            if ($('#view' + id).is(":checked")) { var isView = 1; } else { var isView = 0; }
            if ($('#insert' + id).is(":checked")) { var isInsert = 1; } else { var isInsert = 0; }
            if ($('#update' + id).is(":checked")) { var isUpdate = 1; } else { var isUpdate = 0; }
            if ($('#delete' + id).is(":checked")) { var isDelete = 1; } else { var isDelete = 0; }
            if ($('#default' + id).is(":checked")) { var isDefault = 1; } else { var isDefault = 0; }
            if (isView == 1 || isInsert == 1 || isUpdate == 1 || isDelete == 1 || isDefault == 1) {
                menuArr['menu'] = menu;
                menuArr['view'] = isView;
                menuArr['insert'] = isInsert;
                menuArr['update'] = isUpdate;
                menuArr['delete'] = isDelete;
                menuArr['default'] = isDefault;
                roleArr.push(menuArr);
            }
        }

        var finalRoleArray = JSON.stringify(roleArr)
        $.ajax({
            type: 'post',
            url: baseUrl + "/rolewiserights/saveMenu",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                role: role,
                finalRoleArray: finalRoleArray
            },
            beforeSend: function () {
                $('#submitBtn').prop('disabled', true);
                $('#cancelBtn').prop('disabled', true);
                $('#submitBtn').text('Please wait..');
            },
            success: function (data) {
                $('#submitBtn').text('Submit');
                $('#submitBtn').prop('disabled', false);
                $('#cancelBtn').prop('disabled', false);
                toastr.success('Rights updated successfully', 'Rights', { "progressBar": true });
                location.reload();
            }
        })

    }
}


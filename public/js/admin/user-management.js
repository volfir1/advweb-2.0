$(document).ready(function() {
    console.log('DataTable Initialization');

    var dataTable; // Define dataTable in a scope accessible by all functions

    if (!$.fn.DataTable.isDataTable('#datatable')) {
        dataTable = $('#datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/admin/fetchUsers",
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function(d) {
                    d.search = $('input[type="search"]').val();
                },
                dataSrc: function(json) {
                    console.log('JSON Response:', json); // Log the JSON response to the console
                    if (!json || !json.data) {
                        console.error("Invalid JSON response:", json);
                        return [];
                    }
                    return json.data;
                },
                error: function(xhr, error, thrown) {
                    console.error("Error in fetching data: ", xhr.responseText);
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                {
                    data: 'profile_image',
                    name: 'profile_image',
                    render: function(data, type, full, meta) {
                        if (type === 'display') {
                            return '<img src="' + (data ? data : '/images/default-placeholder.png') + '" alt="Profile Image" class="img-thumbnail rounded-circle" width="30" height="30">';
                        }
                        return data;
                    }
                },
                { data: 'name', name: 'name', title: 'Username' },
                { data: 'fname', name: 'fname', title: 'First Name' },
                { data: 'lname', name: 'lname', title: 'Last Name' },
                { data: 'email', name: 'email' },
                { data: 'contact', name: 'contact' },
                { data: 'address', name: 'address' },
                {
                    data: 'role',
                    name: 'role',
                    render: function(data, type, full, meta) {
                        return data === 'admin' ? 'Admin' : 'Customer';
                    }
                },
                {
                    data: 'active_status',
                    name: 'active_status',
                    render: function(data, type, full, meta) {
                        return data ? 'Active' : 'Inactive';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return '<button class="btn btn-icon edit" data-id="' + row.id + '"><i class="fas fa-edit" style="color: green;"></i></button> ' +
                            '<button class="btn btn-icon delete" data-id="' + row.id + '"><i class="fas fa-trash" style="color: red;"></i></button>';
                    }
                }
            ],
            searching: true,
            language: {
                emptyTable: "No data available in table",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                lengthMenu: "Show _MENU_ entries",
                loadingRecords: "Loading...",
                processing: "Processing...",
                search: "Search:",
                zeroRecords: "No matching records found"
            },
            order: [[0, "desc"]]
        });
    }

    // Handle Add/Edit modal actions
    $('#sample_form').on('submit', function(event) {
        event.preventDefault();
        var action_url = "/api/admin/store";

        if ($('#action').val() == 'Edit') {
            action_url = "/api/admin/updateUserData";
        }

        var formData = {
            id: $('#id').val(),
            active_status: $('#active_status').val(),
            role: $('#role').val(),
            action: $('#action').val()
        };

        console.log('Form Data:', formData); // Log the form data to the console

        $.ajax({
            url: action_url,
            method: "POST",
            data: formData,
            dataType: "json",
            success: function(data) {
                if (data.errors) {
                    // Display validation errors if any
                    $.each(data.errors, function(key, value) {
                        $('#' + key + '_error').html(value);
                    });
                }

                if (data.success) {
                    // Refresh DataTable and close modal on success
                    if (dataTable && dataTable.ajax) {
                        dataTable.ajax.reload(null, false);
                    }
                    $('#action_modal').modal('hide');
                    $('#sample_form')[0].reset();
                    $('#message').html('<div class="alert alert-success">' + data.success + '</div>');
                    setTimeout(function() {
                        $('#message').html('');
                    }, 5000);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.log('Response:', xhr.responseText); // Log the response text to the console
                alert('An error occurred while processing your request. Please try again.');
            }
        });
    });

    // Handle Edit action
    $(document).on('click', '.edit', function() {
        var id = $(this).data('id');

        $('#sample_form').find('.text-danger').html('');

        $.ajax({
            url: "/api/admin/user/" + id,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log('Edit Data:', data); // Log the data received
                $('#name').val(data.name).prop('disabled', true);
                $('#role').val(data.role === 'admin' ? 'admin' : 'customer');
                $('#active_status').val(data.active_status == 1 ? 1 : 0);
                $('#id').val(id);
                $('#dynamic_modal_title').text('Edit User');
                $('#action_button').text('Edit');
                $('#action').val('Edit');
                $('#action_modal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.log('Response:', xhr.responseText); // Log the response text to the console
                alert('An error occurred while fetching user data. Please try again.');
            }
        });
    });

    // Handle Delete action
    $(document).on('click', '.delete', function() {
        var id = $(this).data('id');
        if (confirm("Are you sure you want to delete this user?")) {
            $.ajax({
                url: "/api/admin/users/delete/" + id,
                method: 'DELETE',
                data: { id: id },
                dataType: 'json',
                success: function(data) {
                    // Refresh DataTable on success
                    if (dataTable && dataTable.ajax) {
                        dataTable.ajax.reload(null, false);
                    }
                    $('#message').html('<div class="alert alert-success">' + data.success + '</div>');
                    setTimeout(function() {
                        $('#message').html('');
                    }, 5000);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.log('Response:', xhr.responseText); // Log the response text to the console
                    alert('An error occurred while deleting the user. Please try again.');
                }
            });
        }
    });

    // Handle Import action
    $('#import_excel').click(function() {
        $('#import_form')[0].reset();
        $('#import_message').html('');
        $('#import_modal').modal('show');
    });

    $('#import_form').on('submit', function(event) {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: "/api/admin/import",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(data) {
                if (data.errors) {
                    // Display validation errors
                    $('#import_message').html('<div class="alert alert-danger">' + data.errors + '</div>');
                }

                if (data.success) {
                    // Refresh DataTable on success
                    if (dataTable && dataTable.ajax) {
                        dataTable.ajax.reload(null, false);
                    }
                    $('#import_message').html('<div class="alert alert-success">' + data.success + '</div>');
                    setTimeout(function() {
                        $('#import_message').html('');
                        $('#import_modal').modal('hide');
                    }, 5000);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.log('Response:', xhr.responseText); // Log the response text to the console
                alert('An error occurred while importing the data. Please try again.');
            }
        });
    });

    // Clear Import modal data
    $('#clear_import_data').click(function() {
        $('#file').val('');
        $('#import_message').html('');
    });

    // Handle Export action
    $('#export_excel').click(function() {
        window.location.href = "/api/admin/export";
    });
});

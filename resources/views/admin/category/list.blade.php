<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    
    <title>Category :: MyG</title>
    
    <!-- Include CSS/JS or other head content -->
    @include("admin.include.header")
</head>
<body>
    <!-- Include sidebar/menu -->
    @include("admin.include.sidebar-menu")
    
    <div class="main-area">
        <h2 class="main-heading">All Categories</h2>
        <div class="dash-all">
            <div class="dash-table-all">        
                <div class="sort-block">
                    <a href="{{url('add_category')}}" class="btn btn-primary btn-search">Add Category</a>
                </div>
                <table class="table table-striped category-datatable" id="category-datatable">
                    <thead>
                        <tr>
                            @if(session('Role') === 'Super Admin')
                            <th width="50px">
                                <input type="checkbox" id="select-all">&nbsp;&nbsp;&nbsp;
                                <button class="button_orange fa fa-trash" id="delete-selected"></button>
                            </th>
                            @endif
                            <th width="60px">Sl.</th>
                            <th width="">Category Name</th>
                            <th width="120px">Status</th>
                            <th width="120px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Include JavaScript -->
    <script type="text/javascript">
    $(document).ready(function(){
        @if(session()->has('message'))
        Swal.fire({
            title: "Success!",
            text: "{{ session()->get('message') }}",
            icon: "success",
        }).then(function() {
                // Reload DataTable after SweetAlert confirmation
                $('#category-datatable').DataTable().ajax.reload();
            });
        @endif
        
        $('#select-all').on('change', function() {
            $('input[name="item_checkbox[]"]').prop('checked', $(this).prop('checked'));
        });
        
        // $(function () {
            var table = $('.category-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('get_category_list') }}",
                order: ['1', 'DESC'],
                pageLength: 10,
                columns: [
                    @if(session('Role') === 'Super Admin')
                    { 
                        data: 'checkbox', 
                        name: 'checkbox', 
                        orderable: false, 
                        searchable: false,
                        render: function(data, type, row) {
                            return '<input type="checkbox" name="item_checkbox[]" value="' + row.CategoryID + '">';
                        }
                    },
                    @endif
                    { 
                        data: 'id', 
                        name: 'id', 
                        render: function (data, type, row, meta) {
                            return meta.row + 1; // meta.row is zero-based index
                        }
                    },
                    { data: 'CategoryName', name: 'CategoryName' },
                    { 
                        data: 'Status', 
                        name: 'Status', 
                        render: function(data, type, row) {
                            if (data == 0) {
                                return '<span class="badge badge-success">Inactive</span>';
                            } else if (data == 1) {
                                return '<span class="badge badge-primary">Active</span>';
                            } else {
                                return '<span class="badge badge-danger">Deleted</span>';
                            }
                        }
                    },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });
        });

        function delete_category_modal(id) {
            var id = id; 
            Swal.fire({
                title: 'Are you sure?',
                text: "Are you sure you want to delete this Category?",
                icon: 'warning',
                buttons: true,
                dangerMode: true
            }).then((isConfirm) => {
                if (isConfirm) {
                    $.ajax({
                        type:'GET',
                        url:'{{url("/delete_category")}}/' + id,
                        data: {
                            "_token": "{{ csrf_token() }}",
                        },
                        success:function(data) {
                            Swal.fire({
                                title: "Success!",
                                text: "Category has been deleted!..",
                                icon: "success",
                            }).then(function() {
                                // Reload DataTable after SweetAlert confirmation
                                $('#category-datatable').DataTable().ajax.reload();
                            });
                        }
                    });
                }
            }).then((willCancel) => {
                if (willCancel) {
                    window.location.href = "{{url("claim_category")}}";
                }
            }); 
        }

        $('#delete-selected').on('click', function() {
            var ids = $('input[name="item_checkbox[]"]:checked').map(function() {
                return $(this).val();
            }).get().filter(function(value) {
                return value !== undefined && value !== '';
            });

            if (ids.length > 0) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Are you sure you want to delete this Category?",
                    icon: 'warning',
                    showCancelButton: true, // Show the cancel button
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                dangerMode: true
            }).then((result) => {
                if (result.isConfirmed) {
                        $.ajax({
                            url: '{{url("/delete_multi_category")}}',
                            method: 'POST',
                            data: { 
                                ids: ids,
                                _token: "{{ csrf_token() }}",
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: "Success!",
                                    text: "Selected Category have been deleted!",
                                    icon: "success",
                                }).then(function() {
                                    // Reload DataTable after SweetAlert confirmation
                                    $('#category-datatable').DataTable().ajax.reload();
                                });
                            },
                            error: function(xhr, status, error) {
                                console.error(xhr.responseText);
                            }
                        });
                    }
                }).then((willCancel) => {
                    if (willCancel) {
                        window.location.href = "{{url('claim_category')}}";
                    }
                });
            } else {
                Swal.fire({
                    title: "Error!",
                    text: "No items selected.",
                    icon: "error",
                });
            }
        });
    </script>

    <!-- Include footer or additional scripts -->
    @include("admin.include.footer")
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    
    <title>Policies :: MyG</title>
    
    <!-- Include CSS/JS or other head content -->
    @include("admin.include.header")
</head>
<body>
    <!-- Include sidebar/menu -->
    @include("admin.include.sidebar-menu")
    
    <div class="main-area">
        <h2 class="main-heading">All Policies</h2>
        <div class="dash-all">
            <div class="dash-table-all">   
                <div class="sort-block">
                    <div class="col-1 ml-0 mr-2 pl-0">
                        <a href="{{url('add_policy_management')}}" class="btn btn-primary btn-search" id="Add_Policy_btn">Add Policy</a>
                    </div>
                    <div class="col-3 ml-0 pl-0 d-flex align-items-center">
                        <label class="mr-2 mb-0"><b>Grade:</b></label>
                        <select class="form-control" name="GradeFilter" id="GradeFilter">
                            <option value="">Select</option>
                            @foreach($grades as $grade)
                            <option value="{{ $grade->GradeID }}">{{ $grade->GradeName }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="col-3 ml-0 pl-0 d-flex align-items-center">
                        <label class="mr-2 mb-0" id="Categorylabel"><b>Category:</b></label>
                        <select class="form-control" name="CategoryFilter" id="CategoryFilter">
                            <option value="">Select</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->CategoryID }}">{{ $category->CategoryName }}</option>
                        @endforeach
                        </select>
                    </div>
                    
                    
                </div>
                <table class="table table-striped approved-claim-table" id="category-datatable">
                    <thead>
                        <tr>
                        @if(session('Role') === 'Super Admin')
                            <th width="70px">
                                <input type="checkbox" id="select-all">&nbsp;
                                <button class="button_orange fa fa-trash" id="delete-selected"></button>
                            </th>
                            @endif
                            <th width="60px">Sl.</th>
                            <th width="">Category</th>
                           <th width="">SubCategory Name</th>
			 <th width="120px">Grade Type</th>
                            <th width="160px">Grade Class</th>
                            <th width="150px">Grade Amount</th>
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
            $("#Categorylabel").hide();$("#CategoryFilter").hide();
            $("#GradeFilter").on("change",function(){
                $("#CategoryFilter").hide();$("#Categorylabel").hide();
                if($("#GradeFilter").val() != ""){
                    $("#Categorylabel").show();$("#CategoryFilter").show();
                }
            });
            $("#CategoryFilter, #GradeFilter").on("change",function(){
                var category_id = $("#CategoryFilter").val();
                var grade_id = $("#GradeFilter").val();
                if ($.fn.DataTable.isDataTable('#category-datatable')) {
                        $('#category-datatable').DataTable().clear().destroy();
                    }
                if (grade_id) {
                    // Destroy existing DataTable before reinitializing
                    

                    // Initialize or reinitialize DataTable with new parameters
                    var table = $('#category-datatable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('policy_management_list') }}",
                            data: function(d) {
                                d.category_id = category_id;
                                d.grade_id = grade_id;
                            }
                        },
                        order: [[1, 'DESC']],
                        pageLength: 10,
                        columns: [
                            @if(session('Role') === 'Super Admin')
                            { 
                                data: 'checkbox', 
                                name: 'checkbox', 
                                orderable: false, 
                                searchable: false,
                                render: function(data, type, row) {
                                    return '<input type="checkbox" name="item_checkbox[]" value="' + row.PolicyID + '">';
                                }
                            },
                            @endif
                            { 
                                data: 'PolicyID', 
                                name: 'PolicyID', 
                                render: function(data, type, row, meta) {
                                    return meta.row + 1; // meta.row is zero-based index
                                }
                            },
                            { data: 'CategoryName', name: 'CategoryName' },
                            { data: 'SubCategoryName', name: 'SubCategoryName' },
				{ data: 'GradeType', name: 'GradeType' },
                            { data: 'GradeClass', name: 'GradeClass' },
                            { data: 'GradeAmount', name: 'GradeAmount' },
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
                } else {
                    var table = $('#category-datatable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('policy_management_list') }}",
                            data: function(d) {
                                
                            }
                        },
                        order: [[1, 'DESC']],
                        pageLength: 10,
                        columns: [
                            { 
                                data: 'checkbox', 
                                name: 'checkbox', 
                                orderable: false, 
                                searchable: false,
                                render: function(data, type, row) {
                                    return '<input type="checkbox" name="item_checkbox[]" value="' + row.PolicyID + '">';
                                }
                            },
                            { 
                                data: 'PolicyID', 
                                name: 'PolicyID', 
                                render: function(data, type, row, meta) {
                                    return meta.row + 1; // meta.row is zero-based index
                                }
                            },
                            { data: 'CategoryName', name: 'CategoryName' },
{ data: 'SubCategoryName', name: 'SubCategoryName' },

                            { data: 'GradeType', name: 'GradeType' },
                            { data: 'GradeClass', name: 'GradeClass' },
                            { data: 'GradeAmount', name: 'GradeAmount' },
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
                }
            });


        @if(session()->has('message'))
        Swal.fire({
            title: "Success!",
            text: "{{ session()->get('message') }}",
            icon: "success",
        }).then(function() {
                // Reload DataTable after SweetAlert confirmation
                //$('#category-datatable').DataTable().ajax.reload();
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
                            { 
                                data: 'checkbox', 
                                name: 'checkbox', 
                                orderable: false, 
                                searchable: false,
                                render: function(data, type, row) {
                                    return '<input type="checkbox" name="item_checkbox[]" value="' + row.PolicyID + '">';
                                }
                            },
                            { 
                                data: 'PolicyID', 
                                name: 'PolicyID', 
                                render: function(data, type, row, meta) {
                                    return meta.row + 1; // meta.row is zero-based index
                                }
                            },
                            { data: 'SubCategoryName', name: 'SubCategoryName' },
                            { data: 'GradeType', name: 'GradeType' },
                            { data: 'GradeClass', name: 'GradeClass' },
                            { data: 'GradeAmount', name: 'GradeAmount' },
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
                text: "Are you sure you want to delete this Policy?",
                icon: 'warning',
                buttons: true,
                dangerMode: true
            }).then((isConfirm) => {
                if (isConfirm) {
                    $.ajax({
                        type:'GET',
                        url:'{{url("/delete_policy_management")}}/' + id,
                        data: {
                            "_token": "{{ csrf_token() }}",
                        },
                        success:function(data) {
                            Swal.fire({
                                title: "Success!",
                                text: "Policy has been deleted!..",
                                icon: "success",
                            }).then(function() {
                                // Reload DataTable after SweetAlert confirmation
                                $('#category-datatable').DataTable().ajax.reload();
                            });
                        }
                    });
                }
            }).then((willCancel) => {
                $('#category-datatable').DataTable().ajax.reload();
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
                    text: "Are you sure you want to delete this Policy?",
                    icon: 'warning',
                    showCancelButton: true, // Show the cancel button
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                dangerMode: true
            }).then((result) => {
                if (result.isConfirmed) {
                        $.ajax({
                            url: '{{url("/delete_multi_policy_management")}}',
                            method: 'POST',
                            data: { 
                                ids: ids,
                                _token: "{{ csrf_token() }}",
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: "Success!",
                                    text: "Selected Policies have been deleted!",
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
                    $('#category-datatable').DataTable().ajax.reload();
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

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>Role management :: MyG</title>
@include("admin.include.header")
</head>
<body>
    <!-- Include sidebar/menu -->
    @include("admin.include.sidebar-menu")
  <div class="main-area">    
    <div class="claim-cat-cover max-w20">
      <h4 class="sub-heading">Role management</h4>
      <div class="row justify-content-between pt-3 pb-3">
        <div class="col-4">
          <a href="{{url('add_role')}}" class="btn btn-primary btn-search"><i class="fa fa-plus-square" aria-hidden="true"></i> Assign new role</a>
        </div>
        <!-- <div class="col-4">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search">
            <div class="input-group-append">
              <button class="btn btn-success btn-search" type="submit">Go</button>
            </div>
          </div>
        </div> -->
      </div>
      <table class="table table-striped role-assign-datatable" id="role-assign-datatable">
        <thead>
            <tr>
            @if(session('Role') === 'Super Admin')
            <th width="50px">
                <input type="checkbox" id="select-all">&nbsp;&nbsp;&nbsp;
                
                <button class="button_orange fa fa-trash" id="delete-selected"></button>
                
            </th>
            @endif
          <th width="60px">Sl.</th>
          <th width="150px">Employee ID</th>
          <th width="200px">Name</th>
          <th width="150px">Role</th>
          <th width="120px">Status</th>
          <th width="">Action</th>
        </tr>
        </thead>
        <tbody>
          
          
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- delete confirmation Modal start -->
<div class="modal fade" id="deleteConfirmModal">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content modal-confirm">
      <!-- Modal body -->
      <div class="modal-body">
        <div class="mark-complete-sec">
          <img src="images/delete-icon.svg" class="img-fluid">
          <h6>Are you sure?</h6>
          <p>You won’t be able to revert this</p>
        </div>
      </div>
      
      <!-- Modal footer -->
      <div class="modal-footer justify-content-center">        
        <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Yes Delete it</button>
      </div>
      
    </div>
  </div>
</div>
<!-- delete confirmation Modal end -->
 <!-- Include JavaScript -->
 <script type="text/javascript">
    $(document).ready(function() {
        @if(session()->has('message'))
        Swal.fire({
            title: "Success!",
            text: "{{ session()->get('message') }}",
            icon: "success",
        });
        @endif
        $('#select-all').on('change', function() {
            $('input[name="item_checkbox[]"]').prop('checked', $(this).prop('checked'));
        });
        if ($.fn.DataTable.isDataTable('.role-assign-datatable')) {
            $('#role-assign-datatable').DataTable().clear().destroy();
        }
        var table = $('#role-assign-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('roleList') }}",
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
                        return '<input type="checkbox" name="item_checkbox[]" value="' + row.GradeID + '">';
                    }
                },
                @endif
                { 
                    data: 'id', 
                    name: 'id', 
                    render: function (data, type, row, meta) {
                         return meta.settings._iDisplayStart + meta.row + 1; // meta.row is zero-based index
                    }
                },
                { data: 'EmpID', name: 'EmpID' },
                { data: 'EmpName', name: 'EmpName' },
                { data: 'Role', name: 'Role' },
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
        
        $('#delete-selected').on('click', function() {
            var ids = $('input[name="item_checkbox[]"]:checked').map(function() {
                return $(this).val();
            }).get().filter(function(value) {
                return value !== undefined && value !== '';
            });

            if (ids.length > 0) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Are you sure you want to delete this roles?",
                    icon: 'warning',
                    showCancelButton: true, // Show the cancel button
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                dangerMode: true
            }).then((result) => {
                if (result.isConfirmed) {
                        $.ajax({
                            url: '{{url("/delete_multi_role")}}',
                            method: 'POST',
                            data: { 
                                ids: ids,
                                _token: "{{ csrf_token() }}",
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: "Success!",
                                    text: "Selected User's roles have been deleted!",
                                    icon: "success",
                                }).then(function() {
                                    // Reload DataTable after SweetAlert confirmation
                                    $('#role-assign-datatable').DataTable().ajax.reload();
                                });
                            },
                            error: function(xhr, status, error) {
                                console.error(xhr.responseText);
                            }
                        });
                    }
                }).then((willCancel) => {
                    if (willCancel) {
                        window.location.href = "{{url('role-management')}}";
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
    });
    function delete_role_modal(id) {
            var id = id; 
            Swal.fire({
                title: 'Are you sure?',
                text: "Are you sure you want to delete this grade?",
                icon: 'warning',
                buttons: true,
                dangerMode: true
            }).then((isConfirm) => {
                if (isConfirm) {
                    $.ajax({
                        type:'GET',
                        url:'{{url("/delete_role")}}/' + id,
                        data: {
                            "_token": "{{ csrf_token() }}",
                        },
                        success:function(data) {
                            Swal.fire({
                                title: "Success!",
                                text: "Role has been deleted!..",
                                icon: "success",
                            }).then(function() {
                                // Reload DataTable after SweetAlert confirmation
                                $('#role-assign-datatable').DataTable().ajax.reload();
                            });
                        }
                    });
                }
            }).then((willCancel) => {
                if (willCancel) {
                    window.location.href = "{{url('role-management')}}";
                }
            }); 
        }

       
    </script>

    <!-- Include footer or additional scripts -->
    @include("admin.include.footer")
</body>
</html>


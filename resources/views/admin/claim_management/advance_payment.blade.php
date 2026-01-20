<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>Advance Approval :: MyG</title>
@include("admin.include.header")


@include("admin.include.sidebar-menu")
  <div class="main-area">
    <h2 class="main-heading">Advance Approval</h2>
    <div class="dash-all">
      <div class="dash-table-all">        
        <div class="sort-block">
          
        </div>
        <table class="table table-striped advance-approval-table" id="advance-approval-table">
          <thead>            
            <th>Sl.</th>            
            <th>Date</th>
            <th>Employee name/ID</th>
            <th>Advance Purpose</th>
            <th>Total amount</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Transaction ID</th>
            <th>Action</th>
          </thead>
          <tbody>
            
          </tbody>
        </table>
        <div class="pagination-block">
          
        </div>
      </div>
    </div>
  </div>
</div>

<!-- approve confirmation Modal start -->
<div class="modal fade" id="approveModal">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <!-- Modal body -->
      <form method="POST" >
        <div class="modal-body">
          <div class="mark-complete-sec">
            <img src="images/complete-check.svg" class="img-fluid">
            <h6>Are you sure?</h6>
            <p>You won’t be able to revert this  </p>
          </div>
          <div>
            <input type="hidden" name="id" value="" id="approve_id" required>
            <label>Remarks</label>
            <textarea rows="2" name="remarks" class="form-control" id="approve_remarks" ></textarea>
          </div>
        </div>
        
        <!-- Modal footer -->
        <div class="modal-footer justify-content-center">        
          <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
          <button type="submit" id="approveSubmit" class="btn btn-success" data-dismiss="modal">Approve</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- approve confirmation Modal start -->
<div class="modal fade" id="rejectModal">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
    <form method="POST">
        <div class="modal-body">
          <div class="mark-complete-sec">
            <img src="images/complete-check.svg" class="img-fluid">
            <h6>Are you sure?</h6>
            <p>You won’t be able to revert this  </p>
          </div>
          <div>
          <input type="hidden" name="id" value="" id="reject_id" required>
            <label>Remarks</label>
            <textarea rows="2" name="remarks" class="form-control" id="reject_remarks"></textarea>
          </div>
        </div>
        
        <!-- Modal footer -->
        <div class="modal-footer justify-content-center">        
          <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" id="rejectSubmit" data-dismiss="modal">Reject</button>
        </div>
      </form>
      
    </div>
  </div>
</div>

<!-- approve confirmation Modal start -->
<div class="modal fade" id="settleModal">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
    <form method="POST" >
        <div class="modal-body">
          <div class="mark-complete-sec">
            <img src="images/complete-check.svg" class="img-fluid">
            <h6>Are you sure?</h6>
            <p>You won’t be able to revert this  </p>
          </div>
          <div>
          <input type="hidden" name="id" value="" id="settle_id" required>
            <label>Transaction ID</label>
            <input type="text" name="TransactionID" value="" id="TransactionID" class="form-control" required>
          </div>
        </div>
        
        <!-- Modal footer -->
        <div class="modal-footer justify-content-center">        
          <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" data-dismiss="modal" id="settleSubmit">Settle</button>
        </div>
      </form>
      
    </div>
  </div>
</div>

<script>    
  $(document).ready(function(){
        @if(session()->has('message'))
        Swal.fire({
            title: "Success!",
            text: "{{ session()->get('message') }}",
            icon: "success",
        });
        @endif

        // DataTables script
      
        var table = $('#advance-approval-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('advance_list') }}",
            order: ['1', 'DESC'],
            pageLength: 10,
            columns: [
              { 
                  data: 'id', 
                  name: 'id', 
                  render: function(data, type, row, meta) {
                      return meta.row + 1; // meta.row is zero-based index
                  }
              },
              { data: 'created_at', name: 'created_at' },
              { data: 'UserData', name: 'UserData' },
              { data: 'Purpose', name: 'Purpose' },
              { data: 'TotalAmount', name: 'TotalAmount', className: 'text-right'},
              { data: 'Status', name: 'Status'},
              { data: 'Remarks', name: 'Remarks'},
              { data: 'TransactionID', name: 'TransactionID' },
              { data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });


        $('#approveSubmit').click(function() {
          var id = $('#approve_id').val();
          var approve_remarks = $('#approve_remarks').val();
          $.ajax({
              url: "{{ route('advance_approve') }}",
              type: "POST",
              data: {
                  _token: "{{ csrf_token() }}",
                  id: id,
                  remarks:approve_remarks
              },
              success: function(response) {
                  if(response.success) {
                      $('#approveModal').modal('hide');
                      Swal.fire({
                          title: "Success!",
                          text: "Advance approved successfully",
                          icon: "success",
                      }).then(function() {
                          $('#advance-approval-table').DataTable().ajax.reload();
                      });
                  } else {
                      Swal.fire({
                          title: "Error!",
                          text: "An error occurred while marking the claim as complete.",
                          icon: "error",
                      });
                  }
              },
              error: function(xhr, status, error) {
                  Swal.fire({
                      title: "Error!",
                      text: "Failed to mark claim as complete. Please try again later.",
                      icon: "error",
                  });
              }
          });
        });

        $('#rejectSubmit').click(function() {
         
          var id = $('#reject_id').val();
          var reject_remarks = $('#reject_remarks').val();
          $.ajax({
              url: "{{ route('advance_reject') }}",
              type: "POST",
              data: {
                  _token: "{{ csrf_token() }}",
                  id: id,
                  remarks:reject_remarks
              },
              success: function(response) {
                  if(response.success) {
                      $('#rejectModal').modal('hide');
                      Swal.fire({
                          title: "Success!",
                          text: "Advance rejected successfully",
                          icon: "success",
                      }).then(function() {
                          $('#advance-approval-table').DataTable().ajax.reload();
                      });
                  } else {
                      Swal.fire({
                          title: "Error!",
                          text: "An error occurred while marking the claim as complete.",
                          icon: "error",
                      });
                  }
              },
              error: function(xhr, status, error) {
                  Swal.fire({
                      title: "Error!",
                      text: "Failed to mark claim as complete. Please try again later.",
                      icon: "error",
                  });
              }
          });
        });

        $('#settleSubmit').click(function() {
          var id = $('#settle_id').val();
          var TransactionID = $('#TransactionID').val();
          $.ajax({
              url: "{{ route('advance_settled') }}",
              type: "POST",
              data: {
                  _token: "{{ csrf_token() }}",
                  id: id,
                  TransactionID:TransactionID
              },
              success: function(response) {
                  if(response.success) {
                      $('#settleModal').modal('hide');
                      Swal.fire({
                          title: "Success!",
                          text: "Advance settled successfully",
                          icon: "success",
                      }).then(function() {
                          $('#advance-approval-table').DataTable().ajax.reload();
                      });
                  } else {
                      Swal.fire({
                          title: "Error!",
                          text: "An error occurred while marking the claim as complete.",
                          icon: "error",
                      });
                  }
              },
              error: function(xhr, status, error) {
                  Swal.fire({
                      title: "Error!",
                      text: "Failed to mark claim as complete. Please try again later.",
                      icon: "error",
                  });
              }
          });
        });
    });

 
  function approve(id) {
    $('#approve_id').val(id);
    $('#approve_remarks').val("");
    $('#approveModal').modal('show');
  } 
  function reject(id) {
    $('#reject_id').val(id);
    $('#reject_remarks').val("");
    $('#rejectModal').modal('show');
  } 
  function settle(id) {
    $('#settle_id').val(id);
    $('#TransactionID').val("");
    $('#settleModal').modal('show');
  } 
</script>




<!-- approve confirmation Modal end -->
@include("admin.include.footer")

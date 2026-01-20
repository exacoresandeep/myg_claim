<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>All Approved Claims :: MyG</title>
<style>
.pagination-block{display:none;}
</style>

@include("admin.include.header")

@include("admin.include.sidebar-menu")
<div class="main-area">
    <h2 class="main-heading">All Approved Claims</h2>
    <div class="dash-all">
      <div class="dash-table-all">        
        <div class="sort-block d-flex justify-content-end mb-2"> 
          <a href="#" class="btn btn-success ml-2" id="export-btn"><i class="fa fa-upload" aria-hidden="true"></i> Export For Bank</a>
          <a href="#" class="btn btn-success ml-2" id="import-btn"><i class="fa fa-download" aria-hidden="true"></i> Import to Complete</a>
          <form action="{{ route('import_excel_data') }}" method="POST" enctype="multipart/form-data">
             @csrf
            <input type="file" id="file-input" style="display: none;" accept=".xlsx, .xls">
            </form>
        </div>
        <table class="table table-striped approved-claim-table" id="approved-claim-table">
          <thead>            
            <th>Sl.</th>
            <th>Trip ID</th>
            <th>Date</th>
            <th>Employee Name</th>
            <th>Employee ID</th>
            <th>Type of trip</th>            
            <th>Branch name/code</th>
            <th>Total amount</th>
            <th>Action</th>
          </thead>
          <tbody>
           
          </tbody>
        </table>
       
      </div>
    </div>
 
  
</div>

<!-- approve confirmation Modal start -->
<div class="modal fade" id="markCompleteModal">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <!-- Modal body -->
      <div class="modal-body">
        <div class="mark-complete-sec">
          <img src="images/complete-check.svg" class="img-fluid">
          <h6>Are you sure?</h6>
          <p>You won’t be able to revert this  </p>
        </div>
      </div>
      <input type="hidden" value="" name="" id="modalTripClaimID">   
      <!-- Modal footer -->
      <div class="modal-footer justify-content-center">   
          
        <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" data-dismiss="modal" id="confirmComplete">Complete</button>
      </div>
      
    </div>
  </div>
</div>

<!-- DataTables Buttons JS -->
<script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>

<!-- XLSX Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- JSZip for Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

    <script>
      
      $(document).ready(function(){
        $('#import-btn').on('click', function() {
            $('#file-input').click();
        });

        $('#file-input').on('change', function(event) {
    var file = event.target.files[0];
    if (file) {
        var formData = new FormData();
        formData.append('excel_file', file);

        // Show loader
        Swal.fire({
            title: 'Uploading...',
            text: 'Please wait while your data is being processed.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading(); // Show loader
            }
        });

        // Perform AJAX request to import the data
        $.ajax({
            url: "{{ route('import_excel_data') }}", // Change to your route
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                // You can also use this if you want to do something before the request is sent
            },
            success: function(response) {
                if (response.success) {
                    // Handle success (e.g., show a success message, reload the data table)
                    Swal.fire({
                        title: "Success!",
                        text: "Claims settled successfully.",
                        icon: "success",
                    }).then(function() {
                        $('#approved-claim-table').DataTable().ajax.reload();
                    });
                } else {
                    // Handle error
                    Swal.fire({
                        title: "Error!",
                        text: response.message || "An error occurred while importing the data.",
                        icon: "error",
                    });
                }
            },
            error: function(xhr, status, error) {
                // Handle failure
                Swal.fire({
                    title: "Error!",
                    text: "Failed to import data. Please try again later.",
                    icon: "error",
                });
            }
        });
    }
});

        $('#export-btn').on('click', function() {
            // Get table data
            var data = table.buttons.exportData({
                decodeEntities: false
            });

            // Define the desired column order and add a new column for "Transaction ID"
            var newOrder = [0, 1, 3, 4, 7, 8]; // The last index (7) is for the new column
           
            // Reorder headers and add the new "Transaction ID" header
            var reorderedHeader = newOrder.map(function(i) {
              if (i === 7) {
                  return "Total Amount"; // Change this to your desired header name
              } else if (i === 8) {
                  return "Transaction ID"; // Add the new header for the additional column
              } else {
                  return data.header[i];
              }
            });

            // Reorder body data and add an empty cell for "Transaction ID"
            var reorderedBody = data.body.map(function(row, index) {
                // Replace the TripClaimID with the original from the data table
                row[1] = table.row(index).data().TripClaimID; // Assuming the TripClaimID is in the second column (index 1)
                
                return newOrder.map(function(i) {
                    return i === 8 ? "" : row[i]; // Add an empty string for "Transaction ID"
                });
            });

            // Create workbook and worksheet with reordered data
            var wb = XLSX.utils.book_new();
            var ws = XLSX.utils.aoa_to_sheet([reorderedHeader].concat(reorderedBody));
            
            XLSX.utils.book_append_sheet(wb, ws, "Report Data");
            
            var date = new Date();
            var formattedDate = date.getFullYear() + '-' + 
            ('0' + (date.getMonth() + 1)).slice(-2) + '-' + 
            ('0' + date.getDate()).slice(-2);

            // Generate the file with the date in the filename
            XLSX.writeFile(wb, 'report_data_' + formattedDate + '.xlsx');
        });

           @if(session()->has('message'))
           Swal.fire({
               title: "Success!",
               text: "{{ session()->get('message') }}",
               icon: "success",
           });
           @endif

           // DataTables script
          
           var table = $('#approved-claim-table').DataTable({
               processing: true,
               serverSide: true,
               ajax: "{{ route('approved_claims_list') }}",
               order: ['1', 'DESC'],
               pageLength: 10,
               columns: [
                   { 
                       data: 'id', 
                       name: 'id', 
                       render: function(data, type, row, meta) {
                           return meta.row + 1; // meta.row is zero-based index
                       },
                       className: 'text-center'
                   },
                   { 
                        data: 'TripClaimID', 
                        name: 'TripClaimID', 
                        render: function(data, type, row) {
                            // Modify the TripClaimID to add 'TMG' and remove the first 8 characters
                            return 'TMG' + data.substring(8);
                        }
                    },
                   { data: 'created_at', name: 'created_at' },
                   { data: 'emp_name', name: 'emp_name' },
                   { data: 'emp_id', name: 'emp_id' },
                   { data: 'TripTypeID', name: 'TripTypeID' },
                   { data: 'VisitBranchID', name: 'VisitBranchID' },
                   { data: 'TotalAmount', name: 'TotalAmount', className: 'text-right'},
                   { data: 'action', name: 'action', orderable: false, searchable: false}
               ]
           });


           $('#confirmComplete').click(function() {
                var TripClaimID = $('#modalTripClaimID').val();
                
                // Perform the AJAX request to mark the claim as complete
                $.ajax({
                    url: "{{ route('complete_approved_claim') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        TripClaimID: TripClaimID
                    },
                    success: function(response) {
                        if(response.success) {
                            // Close the modal
                            $('#markCompleteModal').modal('hide');
                            
                            // Show SweetAlert success message
                            Swal.fire({
                                title: "Success!",
                                text: "Claim marked as complete.",
                                icon: "success",
                            }).then(function() {
                                // Reload DataTable after SweetAlert confirmation
                                $('#approved-claim-table').DataTable().ajax.reload();
                            });
                        } else {
                            // Show SweetAlert error message
                            Swal.fire({
                                title: "Error!",
                                text: "An error occurred while marking the claim as complete.",
                                icon: "error",
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Show SweetAlert error message for AJAX error
                        Swal.fire({
                            title: "Error!",
                            text: "Failed to mark claim as complete. Please try again later.",
                            icon: "error",
                        });
                    }
                });
            });
       });

      function openCompleteModal(TripClaimID) {
          $('#modalTripClaimID').val(TripClaimID);
          $('#markCompleteModal').modal('show');
      }

       </script>
 
@include("admin.include.footer")
<body>
  </html>
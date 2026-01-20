<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Report management :: MyG</title>
  <style>
    .pagination-block { display: none; }
  </style>

  <!-- ✅ DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

  @include("admin.include.header")
  @include("admin.include.sidebar-menu")
</head>

<body>
  <div class="main-area">
    <h2 class="main-heading">Report management</h2>
    <div class="dash-all">
      <div class="dash-table-all">
        <div class="filter-block mb-0">
          <div class="row">
            <!-- Filter Fields -->
            @php
              $fields = [
                ['FromDate', 'From', 'date'],
                ['ToDate', 'To', 'date'],
                ['Status', 'Status', 'select', ['Pending', 'Approved', 'Rejected', 'Paid']],
                ['TripType', 'Type of trip', 'select', $tripTypes->pluck('TripTypeName', 'TripTypeID')->toArray()],
                ['EmpID', 'Employee Code', 'text'],
                ['GradeID', 'Grade', 'select', $grades->pluck('GradeName', 'GradeID')->toArray()],
                ['BranchID', 'Branch Visited', 'text'],
              ];
            @endphp

            @foreach($fields as $field)
              <div class="col-md-3">
                <label>{{ $field[1] }}</label>
                @if($field[2] === 'select')
                  <select class="form-control" id="{{ $field[0] }}" name="{{ $field[0] }}">
                    <option value="">Select</option>
                    @foreach($field[3] as $key => $value)
                      <option value="{{ is_numeric($key) ? $value : $key }}">{{ $value }}</option>
                    @endforeach
                  </select>
                @else
                  <input type="{{ $field[2] }}" class="form-control" id="{{ $field[0] }}" name="{{ $field[0] }}">
                @endif
              </div>
            @endforeach

            <input type="hidden" id="HiddenBranchID" name="HiddenBranchID">

            <div class="col-md-3">
              <label>&nbsp;</label>
              <button type="button" class="btn btn-primary btn-search mt-1" id="report-managament-search-btn">Search</button>
            </div>
          </div>
        </div>

       <a href="#" class="btn btn-success mb-1 mt-1" id="export-button"><i class="fa fa-download"></i> Export All</a>
        <table class="table table-striped" id="approved-management-table">
          <thead>
            <tr>
              <th>Sl.</th>
              <th>Trip ID</th>
              <th>Date</th>
              <th>Type of trip</th>
              <th>Employee Name</th>
              <th>Employee ID</th>
              <th>Visit Branch</th>
              <th>Grade</th>
              <th>Department</th>
              <th>Amount</th>
              <th>Deduct Amount</th>
              <th>Status</th>
              <th>Approval Date</th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="10" class="text-center">No Data</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @include("admin.include.footer")

  <!-- ✅ REQUIRED SCRIPTS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
  
   
    $(document).ready(function() {
        $('#export-button').on('click', function(e) {
    e.preventDefault();

    let params = {
      FromDate: $('#FromDate').val(),
      ToDate: $('#ToDate').val(),
      Status: $('#Status').val(),
      TripType: $('#TripType').val(),
      EmpID: $('#EmpID').val(),
      GradeID: $('#GradeID').val(),
      BranchID: $('#HiddenBranchID').val()
    };

    let query = $.param(params);

    window.location.href = "{{ route('report.export') }}?" + query;
  }); 

      $("#EmpID").autocomplete({
        source: function(request, response) {
          $.get("{{ url('/search_role_user') }}", { search: request.term }, function(data) {
            response($.map(data, function(item) {
              return {
                label: item.emp_id + " (" + item.emp_name + ")",
                value: item.emp_id
              };
            }));
          });
        },
        minLength: 2
      });

      // Autocomplete for Branch
      $("#BranchID").autocomplete({
        source: function(request, response) {
          $.get("{{ url('/search_branch') }}", { search: request.term }, function(data) {
            response($.map(data, function(item) {
              return {
                label: item.BranchCode + " (" + item.BranchName + ")",
                value: item.BranchName,
                bid: item.BranchID
              };
            }));
          });
        },
        minLength: 2,
        select: function(event, ui) {
          $('#BranchID').val(ui.item.value);
          $('#HiddenBranchID').val(ui.item.bid);
          return false;
        }
      });

      
      var table = $('#approved-management-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: "{{ route('report_management_list') }}",
          data: function(d) {
            d.FromDate = $('#FromDate').val();
            d.ToDate = $('#ToDate').val();
            d.Status = $('#Status').val();
            d.TripType = $('#TripType').val();
            d.EmpID = $('#EmpID').val();
            d.GradeID = $('#GradeID').val();
            d.BranchID = $('#HiddenBranchID').val();
          }
        },
        
        order: [[1, 'desc']],
        pageLength: 10,
        columns: [
          { data: 'id', render: function(data, type, row, meta) { return meta.row + 1; }, className: 'text-center' },
          { data: 'TripClaimID', render: function(data) { return 'TMG' + data.substring(8); } },
          { data: 'created_at' },
          { data: 'triptype' },
          { data: 'emp_name' },
          { data: 'emp_id' },
          { data: 'Branch' },
          { data: 'Grade' },
          { data: 'Department' },
          { data: 'TotalAmount', className: 'text-right' },
          { data: 'DeductAmount', className: 'text-right' },
          { data: 'Status'},
          { data: 'ApprovalDate'}
        ]
      });

      $('#report-managament-search-btn').click(function() {
        if (!$('#BranchID').val()) {
          $('#HiddenBranchID').val('');
        }
        table.draw();
      });

      // SweetAlert for session message
      @if(session('message'))
        Swal.fire({
          title: "Success!",
          text: "{{ session('message') }}",
          icon: "success",
        });
      @endif
    });
  </script>
</body>
</html>

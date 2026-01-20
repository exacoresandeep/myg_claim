<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>Edit Role :: MyG</title>

<!-- jQuery UI (for autocomplete) -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


@include("admin.include.header")
</head>
<body>
@include("admin.include.sidebar-menu")
<div class="main-area">    
    <div class="claim-cat-cover">
    <div class="back-btn" id="back-button">
        <a href="{{ url('role-management') }}">
          <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Back
        </a>
      </div>
        <h4 class="sub-heading">Edit role</h4>
        <div class="add-cat-cover max-w10">  
            <form method="POST" action="{{url('edit_role_submit')}}">      
            @csrf
                <div class="row">
                    <div class="col-md-6">
                        <label>Enter Employee name/employee ID</label>
                        <input type="text" class="form-control" id="EmpID" name="EmpID" value="{{$data->emp_id}}" required readonly>
                    </div>
                    <div class="col-md-6">
                        <label>Select role type</label>
                        <select class="form-control" name="Role" required>
                            <option value="">Select</option>
                            <option value="Super Admin" {{ $data->emp_role == 'Super Admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="HR & Admin" {{ $data->emp_role == 'HR & Admin' ? 'selected' : '' }}>HR & Admin</option>
                            <option value="Finance" {{ $data->emp_role == 'Finance' ? 'selected' : '' }}>Finance</option>
                            <option value="CMD" {{ $data->emp_role == 'CMD' ? 'selected' : '' }}>CMD Approval</option>
                            <option value="Auditor" {{ $data->emp_role == 'Auditor' ? 'selected' : '' }}>Auditor</option>
                        </select>
                    </div>          
                </div>
                <button type="submit" class="btn btn-primary mt-4">Submit</button>
            </form>
        </div>    
    </div>
</div>

@include("admin.include.footer")


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // Autocomplete feature for the EmpID input field
        $("#EmpID").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ url('/search_role_user') }}",  // URL to the search route
                    type: 'GET',
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.emp_id + " (" + item.emp_name + ")",
                                value: item.emp_id
                            };
                        }));
                    }
                });
            },
            minLength: 2,  // Minimum characters before triggering the search
            select: function(event, ui) {
                $('#EmpID').val(ui.item.value);  // Set the EmpID input to the selected value
                return false;
            }
        });
    });
</script>

</body>
</html>

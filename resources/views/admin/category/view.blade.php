<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Category Preview :: MyG</title>
  
  <!-- Include header -->
  @include("admin.include.header")
  
</head>
<body>
  <!-- Include sidebar menu -->
  @include("admin.include.sidebar-menu")
  
  <div class="main-area">    
    <div class="claim-cover">
    <div class="back-btn" id="back-button">
        <a href="{{ url('claim_category') }}">
          <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Back
        </a>
      </div>
      <div class="bg-cover">
        <table class="table">
          <tr>
            <th width="300">Category ID</th>
            <td width="20">:</td>
            <td>{{$data->CategoryID}}</td>
          </tr>
          <tr>
            <th>Category Name</th>
            <td>:</td>
            <td>{{$data->CategoryName}}</td>
          </tr>
          <tr>
            <th>Trip From</th>
            <td>:</td>
            <td>{{$data->TripFrom ? 'Yes' : 'No'}}</td>
          </tr>
          <tr>
            <th>Trip To</th>
            <td>:</td>
            <td>{{$data->TripTo ? 'Yes' : 'No'}}</td>
          </tr>
          <tr>
            <th>From Date</th>
            <td>:</td>
            <td>{{$data->FromDate ? 'Yes' : 'No'}}</td>
          </tr>
          <tr>
            <th>To Date</th>
            <td>:</td>
            <td>{{$data->ToDate ? 'Yes' : 'No'}}</td>
          </tr>
          <tr>
            <th>Document Date</th>
            <td>:</td>
            <td>{{$data->DocumentDate ? 'Yes' : 'No'}}</td>
          </tr>
          <tr>
            <th>Icon Image</th>
            <td>:</td>
            <td>
              @if($data->ImageUrl)
                <img src="{{ asset('images/category/' . $data->ImageUrl) }}" alt="Category Image" style="max-width: 100px;">
              @else
                No Image
              @endif
            </td>
          </tr>
          <tr>
            <th>Status</th>
            <td>:</td>
            <td>
            <?php if($data->Status == 0)
            {
              echo "Inactive";
            }
            elseif($data->Status == 1)
            {
              echo "Active";
            }
            else
            {
              echo "Deleted";
            }
            ?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    document.getElementById('back-button').addEventListener('click', function(event) {
      event.preventDefault();
      window.history.back();
    });
  </script>
  
  <!-- Include footer -->
  @include("admin.include.footer")
  
</body>
</html>

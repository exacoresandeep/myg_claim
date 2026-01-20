<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>User Preview :: MyG</title>
  
  <!-- Include header -->
  @include("admin.include.header")
  
</head>
<body>
  <!-- Include sidebar menu -->
  @include("admin.include.sidebar-menu")
  
  <div class="main-area">    
    <div class="claim-cover">
      <div class="back-btn" id="back-button">
        <a href="/list_users">
          <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Back
        </a>
      </div>
      <div class="bg-cover">
        <table class="table">
          <!-- <tr>
            <th width="300">Customer ID</th>
            <td width="20">:</td>
            <td>{{$user->id}}</td>
          </tr> -->
        
          <tr>
            <th>Employee ID</th>
            <td>:</td>
            <td>{{$user->emp_id}}</td>
          </tr>

          <tr>
            <th>Employee name</th>
            <td>:</td>
            <td>{{$user->emp_name}}</td>
          </tr>

          <!-- <tr>
            <th>User name</th>
            <td>:</td>
            <td>{{$user->user_name}}</td>
          </tr> -->

          <tr>
            <th>Email</th>
            <td>:</td>
            <td>{{$user->email }}</td>
          </tr>

          <tr>
            <th>Phone number</th>
            <td>:</td>
            <td>{{$user->emp_phonenumber }}</td>
          </tr>


          <tr>
            <th>Department</th>
            <td>:</td>
            <td>{{$user->emp_department }}</td>
          </tr>


          <tr>
            <th>Branch</th>
            <td>:</td>
            <td>{{$user->branchData->BranchName }}</td>
          </tr>


          <tr>
            <th>Base location</th>
            <td>:</td>
            <td>{{$user->baselocationDetails->BranchName }}</td>
          </tr>
<tr>
    <th>Exclude HRMS Base Location Update</th>
    <td>:</td>
    <td>{{ $user->hrms_baselocation_flag ? 'Yes' : 'No' }}</td>
</tr>

          <tr>
            <th>Designation</th>
            <td>:</td>
            <td>{{$user->emp_designation }}</td>
          </tr>


          <tr>
            <th>Grade</th>
            <td>:</td>
            <td>{{$user->emp_grade }}</td>
          </tr>


          <tr>
            <th>Reporting person</th>
            <td>:</td>
            <td>{{$user->reporting_person }} | {{$user->reporting_person_empid }} </td>
          </tr> 
         
<tr>
    <th>Exclude HRMS Reporting Person Update</th>
    <td>:</td>
    <td>{{ $user->hrms_reporting_person_flag ? 'Yes' : 'No' }}</td>
</tr>

          <tr>
            <th>Active</th>
            <td>:</td>
            <td>{{ $user->Status ==1 ? "Active" : "Inactive" }}</td>
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

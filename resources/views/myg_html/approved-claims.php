<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>Approved Claims :: MyG</title>
<?php include("include/header.php");?>

<div class="main-content">
  <?php include("include/menu-left.php");?>
  <div class="main-area">
    <h2 class="main-heading">Approved Claims</h2>
    <div class="dash-all">
      <div class="dash-table-all">        
        <div class="sort-block">
          <div class="show-num">
            <span>Show</span>
            <select class="select">
              <option>20</option>
              <option>50</option>
              <option>100</option>
            </select>
            <span>Entries</span>
          </div> 
          <a href="" class="btn btn-primary">Delete</a>
          <div class="sort-by ml-auto">
            <select class="select">
              <option>Select</option>
              <option>Sort by latest</option>
              <option>Sort by oldest</option>
            </select>
          </div>
        </div>
        <table class="table table-striped">
          <thead>            
            <th>Sl.</th>
            <th>Trip ID</th>
            <th>Date</th>
            <th>Employee name/ID</th>
            <th>Branch name/code</th>
            <th>Type of trip</th>            
            <th>Total amount</th>
            <th>Action</th>
          </thead>
          <tbody>
            <tr>              
              <td>01.</td>
              <td>TID254680</td>
              <td>01/05/2024</td>
              <td>John/MYG45025</td>
              <td>Calicut/MYGC001</td>
              <td>Inauguration</td>
              <td>20,000.00</td>
              <td><a href="" class="btn btn-success" data-toggle="modal" data-target="#markCompleteModal"><i class="fa fa-check-square" aria-hidden="true"></i> Mark as Complete</a></td>
            </tr>
            <tr>              
              <td>01.</td>
              <td>TID254680</td>
              <td>01/05/2024</td>
              <td>John/MYG45025</td>
              <td>Calicut/MYGC001</td>
              <td>Inauguration</td>
              <td>20,000.00</td>
              <td><a href="" class="btn btn-success"><i class="fa fa-check-square" aria-hidden="true"></i> Mark as Complete</a></td>
            </tr>
          </tbody>
        </table>
        <div class="pagination-block">
          <ul class="pagination pagination-sm justify-content-end">
            <li class="page-item"><a class="page-link" href="#">Previous</a></li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">Next</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- approve confirmation Modal start -->
<div class="modal fade" id="markCompleteModal">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <!-- Modal body -->
      <div class="modal-body">
        <div class="mark-complete-sec">
          <img src="images/complete-check.svg" class="img-fluid">
          <h6>Are you sure?</h6>
          <p>You won’t be able to revert this  </p>
        </div>
      </div>
      
      <!-- Modal footer -->
      <div class="modal-footer justify-content-center">        
        <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" data-dismiss="modal">Complete</button>
      </div>
      
    </div>
  </div>
</div>
<!-- approve confirmation Modal end -->

<?php include("include/footer.php");?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>All Claim Requests :: MyG</title>
<?php include("include/header.php");?>

<div class="main-content">
  <?php include("include/menu-left.php");?>
  <div class="main-area">
    <h2 class="main-heading">All Claim Requests</h2>
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
            <th><input type="checkbox" class="checkbox"></th>
            <th>Sl.</th>
            <th>Trip ID</th>
            <th>Date</th>
            <th>Employee name/ID</th>
            <th>Branch name/Code</th>
            <th>Claim interval</th>
            <th>Type of trip</th>
            <th>Total amount</th>
            <th>Action</th>
          </thead>
          <tbody>
            <tr>
              <td><input type="checkbox" class="checkbox"></td>
              <td>01.</td>
              <td>TID254680</td>
              <td>01/05/2024</td>
              <td>John/MYG45025</td>
              <td>Calicut/MYGC001</td>
              <td>01/05/2024-15/05/2024</td>
              <td>Inaguration</td>
              <td>20,000.00</td>
              <td><a href="claim-view.php" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View</a></td>
            </tr>
            <tr>
              <td><input type="checkbox" class="checkbox"></td>
              <td>02.</td>
              <td>TID300400</td>
              <td>10/05/2024</td>
              <td>Abhlash/MYG10052</td>
              <td>Calicut/MYGC001</td>
              <td>01/05/2024-15/05/2024</td>
              <td>Inaguration</td>
              <td>10,000.00</td>
              <td><a href="claim-view.php" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View</a></td>
            </tr>
            <tr>
              <td><input type="checkbox" class="checkbox"></td>
              <td>01.</td>
              <td>TID254680</td>
              <td>01/05/2024</td>
              <td>John/MYG45025</td>
              <td>Calicut/MYGC001</td>
              <td>01/05/2024-15/05/2024</td>
              <td>Inaguration</td>
              <td>20,000.00</td>
              <td><a href="claim-view.php" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> View</a></td>
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

<!-- The Modal -->
<div class="modal fade" id="myModal">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
    
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title text-danger">Are you Sure, you want to delete the file?</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      
      <!-- Modal body -->
      <div class="modal-body">
        Once you delete the file, you will no longer be able to access the file. Click "Yes" to proceed or else click "Cancel".
      </div>
      
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Yes</button>
        <button type="button" class="btn btn-success" data-dismiss="modal">Cancel</button>
      </div>
      
    </div>
  </div>
</div>

<?php include("include/footer.php");?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>Report management :: MyG</title>
<?php include("include/header.php");?>

<div class="main-content">
  <?php include("include/menu-left.php");?>
  <div class="main-area">
    <h2 class="main-heading">Report management</h2>
    <div class="dash-all">
      <div class="dash-table-all">
        <div class="filter-block">
          <div class="row">
            <div class="col-md-3">
              <label>From</label>
              <input type="text" class="form-control" name="" value="01/05/2024">
            </div>
            <div class="col-md-3">
              <label>To</label>
              <input type="text" class="form-control" name="" value="15/05/2024">
            </div>
            <div class="col-md-3">
              <label>Department</label>
              <select class="form-control">
                <option>IT</option>
              </select>
            </div>
            <div class="col-md-3">
              <label>Category</label>
              <select class="form-control">
                <option>Lodging</option>
              </select>
            </div>
            <div class="col-md-3">
              <label>Type of trip</label>
              <select class="form-control">
                <option>Inauguration</option>
              </select>
            </div>
            <div class="col-md-3">
              <label>Employee name</label>
              <input type="text" class="form-control" name="" value="John K (MYG10020)">
            </div>
            <div class="col-md-3">
              <label>Grade</label>
              <select class="form-control">
                <option>Grade 2</option>
              </select>
            </div>
            <div class="col-md-3">
              <label>Branch name</label>
              <input type="text" class="form-control" name="" value="Calicut">
            </div>            
          </div>
          <a href="" class="btn btn-primary btn-search">Search</a>
        </div>      
        <div class="sort-block">
          <div class="show-num border-0">
            <span>Show</span>
            <select class="select">
              <option>20</option>
              <option>50</option>
              <option>100</option>
            </select>
            <span>Entries</span>
          </div> 
          <div class="export-sec">
            <label>Export as</label>
            <div class="input-group">
              <div class="custom-control custom-checkbox mb-3">
                <input type="checkbox" class="custom-control-input" id="customCheck" name="example1">
                <label class="custom-control-label" for="customCheck">CSV</label>
              </div>
              <div class="custom-control custom-checkbox mb-3">
                <input type="checkbox" class="custom-control-input" id="customCheck1" name="example1">
                <label class="custom-control-label" for="customCheck1">PDF</label>
              </div>
              <div class="custom-control custom-checkbox mb-3">
                <input type="checkbox" class="custom-control-input" id="customCheck11" name="example1">
                <label class="custom-control-label" for="customCheck11">Excel</label>
              </div>
            </div>
          </div>
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
            <th>From-to date</th>
            <th>Department</th>
            <th>Grade</th>
            <th>Category</th>
            <th>Type of trip</th>            
            <th>Employee name/ID</th>
            <th>Branch</th>
            <th>Amount</th>
            <th>Action</th>
          </thead>
          <tbody>
            <tr>
              <td><input type="checkbox" class="checkbox"></td>
              <td>01.</td>
              <td>TID254680</td>
              <td>01/05/2024-15/05/2024</td>
              <td>IT</td>
              <td>Grade 2</td>
              <td>Lodging</td>
              <td>Inauguration</td>
              <td>John K (MYG10020)</td>
              <td>Calicut</td>
              <td>550.00 INR</td>
              <td>
                <a href="#" class="btn btn-success"><i class="fa fa-download" aria-hidden="true"></i> Download</a>                
              </td>
            </tr>
            <tr>
              <td><input type="checkbox" class="checkbox"></td>
              <td>02.</td>
              <td>TID123456</td>
              <td>01/05/2024-15/05/2024</td>
              <td>IT</td>
              <td>Grade 2</td>
              <td>Lodging</td>
              <td>Inauguration</td>
              <td>John K (MYG10020)</td>
              <td>Calicut</td>
              <td>1200.00 INR</td>
              <td>
                <a href="#" class="btn btn-success"><i class="fa fa-download" aria-hidden="true"></i> Download</a>                
              </td>
            </tr>
            <tr>
              <td><input type="checkbox" class="checkbox"></td>
              <td>03.</td>
              <td>TID002536</td>
              <td>01/05/2024-15/05/2024</td>
              <td>IT</td>
              <td>Grade 2</td>
              <td>Lodging</td>
              <td>Inauguration</td>
              <td>John K (MYG10020)</td>
              <td>Calicut</td>
              <td>2000.00 INR</td>
              <td>
                <a href="#" class="btn btn-success"><i class="fa fa-download" aria-hidden="true"></i> Download</a>                
              </td>
            </tr>
            <tr>
              <td><input type="checkbox" class="checkbox"></td>
              <td>04.</td>
              <td>TID784512</td>
              <td>01/05/2024-15/05/2024</td>
              <td>IT</td>
              <td>Grade 2</td>
              <td>Lodging</td>
              <td>Inauguration</td>
              <td>John K (MYG10020)</td>
              <td>Calicut</td>
              <td>2000.00 INR</td>
              <td>
                <a href="#" class="btn btn-success"><i class="fa fa-download" aria-hidden="true"></i> Download</a>                
              </td>
            </tr>
            <tr>
              <td><input type="checkbox" class="checkbox"></td>
              <td>05.</td>
              <td>TID895623</td>
              <td>01/05/2024-15/05/2024</td>
              <td>IT</td>
              <td>Grade 2</td>
              <td>Lodging</td>
              <td>Inauguration</td>
              <td>John K (MYG10020)</td>
              <td>Calicut</td>
              <td>2000.00 INR</td>
              <td>
                <a href="#" class="btn btn-success"><i class="fa fa-download" aria-hidden="true"></i> Download</a>                
              </td>
            </tr>
            <tr>
              <td><input type="checkbox" class="checkbox"></td>
              <td>06.</td>
              <td>TID123456</td>
              <td>01/05/2024-15/05/2024</td>
              <td>IT</td>
              <td>Grade 2</td>
              <td>Lodging</td>
              <td>Inauguration</td>
              <td>John K (MYG10020)</td>
              <td>Calicut</td>
              <td>2000.00 INR</td>
              <td>
                <a href="#" class="btn btn-success"><i class="fa fa-download" aria-hidden="true"></i> Download</a>                
              </td>
            </tr>
            <tr>
              <td><input type="checkbox" class="checkbox"></td>
              <td>07.</td>
              <td>TID456789</td>
              <td>01/05/2024-15/05/2024</td>
              <td>IT</td>
              <td>Grade 2</td>
              <td>Lodging</td>
              <td>Inauguration</td>
              <td>John K (MYG10020)</td>
              <td>Calicut</td>
              <td>2000.00 INR</td>
              <td>
                <a href="#" class="btn btn-success"><i class="fa fa-download" aria-hidden="true"></i> Download</a>                
              </td>
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

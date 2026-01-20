<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>User management :: MyG</title>
<?php include("include/header.php");?>

<div class="main-content">
  <?php include("include/menu-left.php");?>
  <div class="main-area">    
    <div class="claim-cat-cover max-w10">
      <h4 class="sub-heading">User management</h4>
      <div class="row justify-content-between pt-3 pb-3">        
        <div class="col-4 ml-auto">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search">
            <div class="input-group-append">
              <button class="btn btn-success btn-search" type="submit">Go</button>
            </div>
          </div>
        </div>
      </div>
      <table class="table table-striped role-edit">
        <thead>
          <th width="5%">Sl.</th>
          <th width="30%">Employee ID</th>
          <th width="20%">Employee name</th>
          <th width="20%">Status</th>
          <th width="20%">Action</th>
        </thead>
        <tbody>
          <tr>
            <td>1.</td>
            <td>John K</td>
            <td>MYG1200</td>
            <td><badge class="badge badge-success">Active</badge></td>
            <td>
              <a href="profile-edit.php" class="btn btn-info"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>              
            </td>
          </tr>
          <tr>
            <td>2.</td>
            <td>Abhilash</td>
            <td>MYG1500</td>
            <td><badge class="badge badge-success">Active</badge></td>
            <td>
              <a href="" class="btn btn-info"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</a>              
            </td>
          </tr>
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

<?php include("include/footer.php");?>

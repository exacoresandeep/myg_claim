<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>Add new trip type :: MyG</title>
<?php include("include/header.php");?>

<div class="main-content">
  <?php include("include/menu-left.php");?>
  <div class="main-area">
    <div class="back-btn">
      <a href="#">
        <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Back
      </a>
    </div>
    <div class="claim-cat-cover">
      <h4 class="sub-heading">Add new trip type</h4>
      <div class="add-cat-cover max-w10">        
        <div class="row">
          <div class="col-md-6">
            <label>Enter new trip type</label>
            <input type="text" class="form-control" name="">
          </div>         
        </div>
        <a href="" class="btn btn-primary mt-4">Submit</a>
      </div>    
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

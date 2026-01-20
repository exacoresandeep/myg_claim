<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>Claim Category management :: MyG</title>
<?php include("include/header.php");?>

<div class="main-content">
  <?php include("include/menu-left.php");?>
  <div class="main-area">    
    <div class="claim-cat-cover max-w10">
      <h4 class="sub-heading">Claim Category management</h4>
      <div class="row justify-content-between pt-3 pb-3">
        <div class="col-4">
          <a href="add-new-category.php" class="btn btn-success btn-add"><i class="fa fa-plus-square" aria-hidden="true"></i> Add Category policy</a>
        </div>
        <div class="col-4">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search">
            <div class="input-group-append">
              <button class="btn btn-success btn-search" type="submit">Go</button>
            </div>
          </div>
        </div>
      </div>
      <table class="table table-striped">
        <thead>
          <th width="5%">Sl.</th>
          <th width="60%">Category name</th>
          <th width="25%">Action</th>
        </thead>
        <tbody>
          <tr>
            <td>1.</td>
            <td>Air</td>
            <td>
              <a href="" class="btn btn-info"><i class="fa fa-pencil-square" aria-hidden="true"></i> Edit</a>
              <a href="" class="btn btn-danger" data-toggle="modal" data-target="#deleteConfirmModal"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a>
            </td>
          </tr>
          <tr>
            <td>2.</td>
            <td>Train</td>
            <td>
              <a href="" class="btn btn-info"><i class="fa fa-pencil-square" aria-hidden="true"></i> Edit</a>
              <a href="" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a>
            </td>
          </tr>
          <tr>
            <td>3.</td>
            <td>Food</td>
            <td>
              <a href="" class="btn btn-info"><i class="fa fa-pencil-square" aria-hidden="true"></i> Edit</a>
              <a href="" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a>
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

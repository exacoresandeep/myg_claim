<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>Profile :: MyG</title>
<?php include("include/header.php");?>

<div class="main-content">
  <?php include("include/menu-left.php");?>
  <div class="main-area">    
    <div class="claim-cat-cover max-w10">
      <h4 class="sub-heading">Profile</h4>      
      <table class="table table-striped role-edit">        
        <tr>
          <td width="30%">Employee name/ID</td>
          <td width="5%">:</td>            
          <td>Basil (MYG12589)</td>
        </tr>
        <tr>
          <td>Department</td>
          <td>:</td>            
          <td>HR</td>
        </tr>
        <tr>
          <td>Branch</td>
          <td>:</td>            
          <td>
            <input type="text" name="" value="Thrissur (TSR)" class="form-control">
          </td>
        </tr>
        <tr>
          <td>Base Location</td>
          <td>:</td>            
          <td>Thrissur (TSR)</td>
        </tr>
        <tr>
          <td>Designation</td>
          <td>:</td>            
          <td>Assistant manager</td>
        </tr>
        <tr>
          <td>Grade</td>
          <td>:</td>            
          <td>Grade 2</td>
        </tr>
        <tr>
          <td>Reporting person</td>
          <td>:</td>            
          <td>
            <input type="text" class="form-control" name="" value="Adham (MYG10020)">
          </td>
        </tr>
        <tr>
          <td>Email ID</td>
          <td>:</td>            
          <td>basil4587@myg.com</td>
        </tr>
        <tr>
          <td>Mobile number</td>
          <td>:</td>            
          <td>9876543210</td>
        </tr>
      </table>
      <a href="" class="btn btn-primary mt-4">Update</a>
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

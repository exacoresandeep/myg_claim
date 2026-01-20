<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>Add New Catrgory :: MyG</title>
<?php include("include/header.php");?>

<div class="main-content">
  <?php include("include/menu-left.php");?>
  <div class="main-area">    
    <div class="claim-cat-cover">
      <h4 class="sub-heading">Add New category policy</h4>
      <div class="add-cat-cover max-w20">
        <label>Category name</label>
        <input type="text" class="form-control input-w" name="">
        <div class="input-group row mb-3 mt-3">
          <div class="col-2">
            <div class="custom-control custom-checkbox mb-3">
              <input type="checkbox" class="custom-control-input" id="customCheck5" name="example5">
              <label class="custom-control-label" for="customCheck5">All</label>
            </div>
          </div>
          <div class="col-2">
            <div class="custom-control custom-checkbox mb-3">
              <input type="checkbox" class="custom-control-input" id="customCheck1" name="example1">
              <label class="custom-control-label" for="customCheck1">Grade 1</label>
            </div>
          </div>
          <div class="col-2">
            <div class="custom-control custom-checkbox mb-3">
              <input type="checkbox" class="custom-control-input" id="customCheck2" name="example2">
              <label class="custom-control-label" for="customCheck2">Grade 2</label>
            </div>
          </div>
          <div class="col-2">
            <div class="custom-control custom-checkbox mb-3">
              <input type="checkbox" class="custom-control-input" id="customCheck3" name="example3">
              <label class="custom-control-label" for="customCheck3">Grade 3</label>
            </div>
          </div>
          <div class="col-2">
            <div class="custom-control custom-checkbox mb-3">
              <input type="checkbox" class="custom-control-input" id="customCheck4" name="example4">
              <label class="custom-control-label" for="customCheck4">Grade 4</label>
            </div>
          </div>
          <div class="col-2">
            <div class="custom-control custom-checkbox mb-3">
              <input type="checkbox" class="custom-control-input" id="customCheck5" name="example5">
              <label class="custom-control-label" for="customCheck5">Grade 5</label>
            </div>
          </div>
        </div>
        <hr>
        <div class="grade-section">
          <table class="table">
            <thead>
              <th>Grades</th>
              <th>Class/Name</th>
              <th>Approval needed/not</th>
              <th>Approver</th>
              <th>Action</th>
            </thead>
            <tbody>
              <tr>
                <td>
                  <input type="text" class="form-control" name="" value="Grade1" disabled>
                </td>
                <td>
                  <input type="text" class="form-control" name="">
                </td>
                <td>
                  <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider round"></span>
                  </label>
                </td>
                <td>
                  <select class="form-control">
                    <option>CMD Approval</option>
                  </select>
                </td>
                <td>
                  <a href=""><i class="fa fa-trash" aria-hidden="true"></i></a>
                </td>
              </tr>
              <tr>
                <td>
                  <input type="text" class="form-control" name="">
                </td>
                <td>
                  <input type="text" class="form-control" name="">
                </td>
                <td>
                  <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider round"></span>
                  </label>
                </td>
                <td>
                  <select class="form-control">
                    <option>CMD Approval</option>
                  </select>
                </td>
                <td>
                  <a href=""><i class="fa fa-trash" aria-hidden="true"></i></a>
                </td>
              </tr>
              <tr>
                <td>
                  <input type="text" class="form-control" name="">
                </td>
                <td>
                  <input type="text" class="form-control" name="">
                </td>
                <td>
                  <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider round"></span>
                  </label>
                </td>
                <td>
                  <select class="form-control">
                    <option>CMD Approval</option>
                  </select>
                </td>
                <td>
                  <a href=""><i class="fa fa-trash" aria-hidden="true"></i></a>
                </td>
              </tr>
              <tr>
                <td>
                  <input type="text" class="form-control" name="">
                </td>
                <td>
                  <input type="text" class="form-control" name="">
                </td>
                <td>
                  <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider round"></span>
                  </label>
                </td>
                <td>
                  <select class="form-control">
                    <option>CMD Approval</option>
                  </select>
                </td>
                <td>
                  <a href=""><i class="fa fa-trash" aria-hidden="true"></i></a>
                </td>
              </tr>
              <tr>
                <td>
                  <input type="text" class="form-control" name="">
                </td>
                <td>
                  <input type="text" class="form-control" name="">
                </td>
                <td>
                  <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider round"></span>
                  </label>
                </td>
                <td>
                  <select class="form-control">
                    <option>CMD Approval</option>
                  </select>
                </td>
                <td>
                  <a href=""><i class="fa fa-trash" aria-hidden="true"></i></a>
                </td>
              </tr>
            </tbody>
          </table>
          <a href="" class="btn btn-primary">Submit</a>
        </div>
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

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>Claim Preview :: MyG</title>
<?php include("include/header.php");?>

<div class="main-content">
  <?php include("include/menu-left.php");?>
  <div class="main-area">    
    <div class="claim-cover">
      <div class="back-btn">
        <a href="#">
          <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Back
        </a>
      </div>
      <div class="bg-cover">
        <table class="table">
          <tr>
            <th width="300">Trip ID</th>
            <td width="20">:</td>
            <td>#TID200300</td>
          </tr>
          <tr>
            <th>Date</th>
            <td width="20">:</td>
            <td>01/05/2024</td>
          </tr>
          <tr>
            <th>Employee ID/Name</th>
            <td width="20">:</td>
            <td>John/MYG5001</td>
          </tr>
          <tr>
            <th>Base Location</th>
            <td width="20">:</td>
            <td>Calicut/MYG001</td>
          </tr>
          <tr>
            <th>Claim Interval</th>
            <td width="20">:</td>
            <td>01/05/2024-15/05/2024</td>
          </tr>
          <tr>
            <th>Type of Trip</th>
            <td width="20">:</td>
            <td>Inauguration</td>
          </tr>
          <tr>
            <th>Branch Name</th>
            <td width="20">:</td>
            <td>Calicut/MYG0001</td>
          </tr>
          <tr>
            <th>Purpose of Trip</th>
            <td width="20">:</td>
            <td>Eranakulam branch inaguration</td>
          </tr>
          <tr>
            <th>Status</th>
            <td width="20">:</td>
            <td><label>Recieved</label></td>
          </tr>
          <tr>
            <th>Total Amount</th>
            <td width="20">:</td>
            <td><span class="amount text-primary"><b>20,000 INR</b></span></td>
          </tr>
          <tr>
            <th>Advance Amount</th>
            <td width="20">:</td>
            <td>15,000 INR</td>
          </tr>
        </table>
      </div>
      <h4 class="sub-heading">Reporting Person Approval Section</h4>
      <div class="bg-cover">
        <table class="table">
          <tr>
            <th width="300">Reporting person name</th>
            <td width="20">:</td>
            <td>Krishnakumar/MYG500900</td>
          </tr>
          <tr>
            <th>Date of approval</th>
            <td width="20">:</td>
            <td>16/05/2024</td>
          </tr>
          <tr>
            <th>Comments</th>
            <td width="20">:</td>
            <td>Your claims has been approved.</td>
          </tr>          
        </table>
      </div>
      <h4 class="sub-heading">Claim Section</h4>
      <div class="category-section">
        <div id="accordion">
          <div class="card">
            <div class="card-header" id="headingOne">
              <h5 class="mb-0">
                <button class="btn btn-link" data-toggle="collapse" data-target="#categoryOne" aria-expanded="true" aria-controls="categoryOne">
                  <span>Air Expenses</span><b>Total Amount : 15,000.00 INR</b>
                  <i class="fa fa-angle-down" aria-hidden="true"></i>
                </button>
              </h5>
            </div>

            <div id="categoryOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
              <div class="card-body">
                <table class="table table-striped table-bordered">
                  <thead>
                    <th width="20">Sl.</th>
                    <th width="100">Travel date</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Ticket class</th>
                    <th width="50">No. of employees</th>
                    <th width="150">Employee IDs/Name</th>
                    <th width="200">Remarks</th>
                    <th>Attached file</th>
                    <th>Amount</th>
                    <th width="100">Action</th>
                  </thead>
                  <tbody>
                    <tr>
                      <td>1.</td>
                      <td>01/05/2024</td>
                      <td>Cochin</td>
                      <td>Trivandrum</td>
                      <td>
                        <span class="badge badge-primary">Economy</span>
                      </td>
                      <td>1</td>
                      <td>
                        <span class="badge badge-dark">MYG1234/John</span>
                      </td>
                      <td><small>Economy Class</small></td>
                      <td>
                        <label><i class="fa fa-paperclip" aria-hidden="true"></i> filename.jpg</label>
                        <a href="" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                        <a href="" class="btn btn-info"><i class="fa fa-download" aria-hidden="true"></i></a>
                      </td>
                      <td>
                        <span class="value">10,000.00</span>
                        <a href="#" class="update">Update</a>
                      </td>
                      <td>
                        <button class="btn btn-success" data-toggle="modal" data-target="#approveModal">Approve</button>
                        <button class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">Reject</button>
                      </td>
                    </tr>
                    <tr>
                      <td>2.</td>
                      <td>05/05/2024</td>
                      <td>Calicut</td>
                      <td>Trivandrum</td>
                      <td>
                        <span class="badge badge-primary">Economy</span>
                      </td>
                      <td>2</td>
                      <td>
                        <span class="badge badge-dark">MYG1234/John</span>
                        <span class="badge badge-dark">MYG7890/Abhilash</span>
                      </td>
                      <td><small>2 Economy Class</small></td>
                      <td>
                        <label><i class="fa fa-paperclip" aria-hidden="true"></i> filename.jpg</label>
                        <a href="" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                        <a href="" class="btn btn-info"><i class="fa fa-download" aria-hidden="true"></i></a>
                      </td>
                      <td>
                        <span class="value">15,000.00 <i class="fa fa-check-circle text-success" aria-hidden="true"></i></span>
                        <a href="" class="btn btn-info btn-view" data-toggle="modal" data-target="#viewSpecialApprovalModal">View</a>
                      </td>
                      <td>
                        <button class="btn btn-success">Approve</button>
                        <button class="btn btn-danger">Reject</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header" id="headingTwo">
              <h5 class="mb-0">
                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#categoryTwo" aria-expanded="false" aria-controls="categoryTwo">
                  <span>Train Expenses</span><b>Total Amount : 15,000.00 INR</b>
                  <i class="fa fa-angle-down" aria-hidden="true"></i>
                </button>
              </h5>
            </div>
            <div id="categoryTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
              <div class="card-body">
                <table class="table table-striped table-bordered">
                  <thead>
                    <th width="20">Sl.</th>
                    <th width="100">Travel date</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Ticket class</th>
                    <th width="50">No. of employees</th>
                    <th width="150">Employee IDs/Name</th>
                    <th width="200">Remarks</th>
                    <th>Attached file</th>
                    <th>Amount</th>
                    <th width="100">Action</th>                    
                  </thead>
                  <tbody>
                    <tr>
                      <td>1.</td>
                      <td>05/05/2024</td>
                      <td>Calicut</td>
                      <td>Trivandrum</td>
                      <td>
                        <span class="badge badge-primary">Economy</span>
                      </td>
                      <td>2</td>
                      <td>
                        <span class="badge badge-dark">MYG1234/John</span>
                        <span class="badge badge-dark">MYG7890/Abhilash</span>
                      </td>
                      <td><small>2 Economy Class</small></td>
                      <td>
                        <label><i class="fa fa-paperclip" aria-hidden="true"></i> filename.jpg</label>
                        <a href="" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i></a>
                        <a href="" class="btn btn-info"><i class="fa fa-download" aria-hidden="true"></i></a>
                      </td>
                      <td>
                        <span class="value">15,000.00</span>
                        <a href="#" class="update" data-toggle="modal" data-target="#updateClaimAmountModal">Update</a>
                      </td>
                      <td>
                        <button class="btn btn-success">Approve</button>
                        <button class="btn btn-danger">Reject</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header" id="headingThree">
              <h5 class="mb-0">
                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#categoryThree" aria-expanded="false" aria-controls="categoryThree">                  
                  <span>Lodging (Outstation)/Food, Conveyance</span><b>Total Amount : 15,000.00 INR</b>
                  <i class="fa fa-angle-down" aria-hidden="true"></i>
                </button>
              </h5>
            </div>
            <div id="categoryThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
              <div class="card-body">
                food
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header" id="headingFour">
              <h5 class="mb-0">
                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#categoryFour" aria-expanded="false" aria-controls="categoryFour">                  
                  <span>Mode of Conveyance Expenses</span><b>Total Amount : 15,000.00 INR</b>
                  <i class="fa fa-angle-down" aria-hidden="true"></i>
                </button>
              </h5>
            </div>
            <div id="categoryFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordion">
              <div class="card-body">
                food
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header" id="headingFive">
              <h5 class="mb-0">
                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#categoryFive" aria-expanded="false" aria-controls="categoryFive">                  
                  <span>Food, Conveyance * Miscellaneous Expenses</span><b>Total Amount : 15,000.00 INR</b>
                  <i class="fa fa-angle-down" aria-hidden="true"></i>
                </button>
              </h5>
            </div>
            <div id="categoryFive" class="collapse" aria-labelledby="headingFive" data-parent="#accordion">
              <div class="card-body">
                food
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header" id="headingSix">
              <h5 class="mb-0">
                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#categorySix" aria-expanded="false" aria-controls="categorySix">                  
                  <span>Bus Fare Expenses</span><b>Total Amount : 15,000.00 INR</b>
                  <i class="fa fa-angle-down" aria-hidden="true"></i>
                </button>
              </h5>
            </div>
            <div id="categorySix" class="collapse" aria-labelledby="headingSix" data-parent="#accordion">
              <div class="card-body">
                food
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header" id="headingSeven">
              <h5 class="mb-0">
                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#categorySeven" aria-expanded="false" aria-controls="categorySeven">                  
                  <span>Petrol reimbursement / Conveyance (Outstation)</span><b>Total Amount : 15,000.00 INR</b>
                  <i class="fa fa-angle-down" aria-hidden="true"></i>
                </button>
              </h5>
            </div>
            <div id="categorySeven" class="collapse" aria-labelledby="headingSeven" data-parent="#accordion">
              <div class="card-body">
                food
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header" id="headingEight">
              <h5 class="mb-0">
                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#categoryEight" aria-expanded="false" aria-controls="categoryEight">                
                  <span>Client Entertainment</span><b>Total Amount : 15,000.00 INR</b>
                  <i class="fa fa-angle-down" aria-hidden="true"></i>
                </button>
              </h5>
            </div>
            <div id="categoryEight" class="collapse" aria-labelledby="headingEight" data-parent="#accordion">
              <div class="card-body">
                food
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Finance Approval Section --->
      <h4 class="sub-heading">Finance user Approval Section</h4>
      <div class="bg-cover approver-section bg-grey">
        <table class="table">
          <tr>
            <th width="300">Advance settled</th>
            <td width="20">:</td>
            <td><b>15,000.00 INR</b></td>
          </tr>
          <tr>
            <th>Amount to be settled</th>
            <td width="20">:</td>
            <td><b>15,000.00 INR</b></td>
          </tr>
          <tr>
            <th>Approver ID</th>
            <td width="20">:</td>
            <td>MYG1234</td>
          </tr>
          <tr>
            <th>Approver name</th>
            <td width="20">:</td>
            <td>Dilin</td>
          </tr>
          <tr>
            <th>Date</th>
            <td width="20">:</td>
            <td>16/05/2024</td>
          </tr>
          <tr>
            <th>Comments</th>
            <td width="20">:</td>
            <td>
              <textarea rows="2" class="form-control"></textarea>
            </td>
          </tr>
          <tr>
            <th>&nbsp;</th>
            <td width="20">&nbsp;</td>
            <td>
              <div class="button-group">
                <button class="btn btn-success">Submit</button>
                <button class="btn btn-danger">Reject All</button>
              </div>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Special Approval Modal start -->
<div class="modal fade myg-modal" id="viewSpecialApprovalModal">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
    
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Special approval View</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      
      <!-- Modal body -->
      <div class="modal-body">
        <h4>Special approval request for <span class="text-primary">Lodging</span></h4>
        <table class="table">
          <tr>
            <th width="30%">Trip ID</th>
            <td width="5%">:</td>
            <td width="65%">TMG12345</td>
          </tr>
          <tr>
            <th>Employee ID/name</th>
            <td>:</td>
            <td>BASIL(MYGX-1234)</td>
          </tr>
          <tr>
            <th>Type of trip</th>
            <td>:</td>
            <td>Inauguration</td>
          </tr>
          <tr>
            <th>Category</th>
            <td>:</td>
            <td>Lodging</td>
          </tr>
          <tr>
            <th>Check In </th>
            <td>:</td>
            <td>20/03/2024</td>
          </tr>
          <tr>
            <th>Check Out</th>
            <td>:</td>
            <td>21/03/2024</td>
          </tr>
          <tr>
            <th>Remarks</th>
            <td>:</td>
            <td>AC room</td>
          </tr>
          <tr>
            <th>Total amount</th>
            <td>:</td>
            <td>3500.00 INR</td>
          </tr>
        </table>
        <hr>
        <h4>Reporting person section</h4>
        <table class="table">
          <tr>
            <th width="30%">Special approval requested by</th>
            <td width="5%">:</td>
            <td width="65%">Adham(MYGX-0000)</td>
          </tr>
          <tr>
            <th>Remarks</th>
            <td>:</td>
            <td>Request for special approval for lodging</td>
          </tr>            
        </table>
        <hr>
        <h4>Special approver section</h4>
        <table class="table">
          <tr>
            <th width="30%">Special approver name</th>
            <td width="5%">:</td>
            <td width="65%">Adham(MYGX-0000)</td>
          </tr>
          <tr>
            <th>Remarks</th>
            <td>:</td>
            <td>Special approval has been approved for lodging</td>
          </tr>
          <tr>
            <th>Eligible amount</th>
            <td>:</td>
            <td><span class="text-primary"><b>2500.00 INR</b></span></td>
          </tr>
          <tr>
            <th>Additional amount</th>
            <td>:</td>
            <td><span class="text-primary"><b>1000.00 INR</b></span></td>
          </tr>
        </table>
      </div>
      
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
      
    </div>
  </div>
</div>
<!-- Special Approval Modal end -->

<!-- update claim amount Modal start -->
<div class="modal fade myg-modal" id="updateClaimAmountModal">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
    
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Update Claim Amount</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      
      <!-- Modal body -->
      <div class="modal-body">
        <table class="table">
          <tr>
            <th width="30%">Submitted amount</th>
            <td width="5%">:</td>
            <td width="65%">9000.00 INR</td>
          </tr>
          <tr>
            <th>Update amount</th>
            <td>:</td>
            <td>
              <input type="text" class="form-control" name="">
            </td>
          </tr>        
        </table>
        <label>Description</label>
        <textarea class="form-control" rows="3"></textarea>
      </div>
      
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Update</button>
      </div>
      
    </div>
  </div>
</div>
<!-- update claim amount Modal end -->

<!-- Approve Modal start -->
<div class="modal fade myg-modal" id="approveModal">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
    
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Enter description:</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      
      <!-- Modal body -->
      <div class="modal-body">                
        <textarea class="form-control" rows="3"></textarea>
      </div>
      
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" data-dismiss="modal">Approve</button>
      </div>
      
    </div>
  </div>
</div>
<!-- Approve Modal Modal end -->

<!-- Reject Modal start -->
<div class="modal fade myg-modal" id="rejectModal">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
    
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Enter reason for rejection:</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      
      <!-- Modal body -->
      <div class="modal-body">                
        <textarea class="form-control" rows="3"></textarea>
      </div>
      
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Reject</button>
      </div>
      
    </div>
  </div>
</div>
<!-- Reject Modal Modal end -->

<?php include("include/footer.php");?>

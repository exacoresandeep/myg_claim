<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>Claim View :: MyG</title>
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
        <table class="table myg-table">
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
        <table class="table myg-table">
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
        <table class="table table-striped table-bordered">
          <thead>
            <th>Sl.</th>
            <th>Category</th>            
            <th>Submitted amount</th>
            <th>Approved amount</th>
            <th>Deducted amount</th>
          </thead>
          <tbody>
            <tr>
              <td>1.</td>
              <td>Air</td>
              <td>15,000.00 INR</td>
              <td>15,000.00 INR</td>
              <td>0</td>
            </tr>
            <tr>
              <td>2.</td>
              <td>Train</td>
              <td>900.00 INR</td>
              <td>500.00 INR</td>
              <td>400.00 INR</td>
            </tr>
            <tr>
              <td>3.</td>
              <td>Food</td>
              <td>3,000.00 INR</td>
              <td>3,000.00 INR</td>
              <td>0</td>
            </tr>
            <tr>
              <td>4.</td>
              <td>Lodging</td>
              <td>2,000.00 INR</td>
              <td>1,000.00 INR</td>
              <td>1,000.00 INR</td>
            </tr>
            <tr style="font-weight: bold;">
              <td colspan="2">Total</td>
              <td>20,900.00 INR</td>
              <td>19,800.00 INR</td>
              <td>1,400.00 INR</td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Finance Approval Section --->
      <h4 class="sub-heading">Finance user Approval Section</h4>
      <div class="bg-cover approver-section bg-grey">
        <table class="table myg-table">
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
              <p>
                Your claim Id #CID-4587892 has been approved
              </p>
            </td>
          </tr>
          <tr>
            <th>&nbsp;</th>
            <td width="20">&nbsp;</td>
            <td>
              <div class="button-group">                
                <button class="btn btn-success">Approve</button>
                <button class="btn btn-danger">Reject</button>
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

<?php include("include/footer.php");?>

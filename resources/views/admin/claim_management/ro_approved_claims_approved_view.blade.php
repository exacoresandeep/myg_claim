<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>Claim Preview :: MyG</title>
@include("admin.include.header")


@include("admin.include.sidebar-menu")
  <div class="main-area">    
    <div class="claim-cover">
      <div class="back-btn">
      <a href="{{ url('ro_approved_claims')}}">
          <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Back
        </a>
      </div>
      <h2 class="main-heading">Approval Confirmation</h2>
      <div class="bg-cover">
      <table class="table">
          <tr>
            <th width="300">Trip ID</th>
            <td width="20">:</td>
            <td>{{ 'TMG' . substr($data->TripClaimID, 8) }}</td>
          </tr>
          <tr>
            <th>Date</th>
            <td width="20">:</td>
            <td><?php echo date('d-m-Y', strtotime($data->created_at));?></td>
          </tr>
          <tr>
            <th>Employee ID/Name</th>
            <td width="20">:</td>
            <td>{{$data->userdata->emp_id}}/{{$data->userdata->emp_name}}</td>
          </tr>
          <tr>
            <th>Designation</th>
            <td width="20">:</td>
            <td>{{$data->userdata->emp_designation}}/{{$data->userdata->emp_designation}}</td>
          </tr>
          <tr>
            <th>Base Location</th>
            <td width="20">:</td>
            <td>@if($result['user_details'] && $result['user_details']['emp_baselocation'])
            {{$result['user_details']['emp_baselocation']}}
              @else
                  N/A
              @endif</td>
          </tr>
          
          <tr>
            <th>Branch Name</th>
            <td width="20">:</td>
            <td>@if($result['user_details'] && $result['user_details']['emp_branch'])
            {{$result['user_details']['emp_branch']}}
              @else
                  N/A
              @endif</td>
          </tr>
          <tr>
            <th>Type of Trip</th>
            <td width="20">:</td>
            <td>{{$data->triptypedetails->TripTypeName}}</td>
          </tr>
          <tr>
            <th>Purpose of Trip</th>
            <td width="20">:</td>
            <td>{{$data->TripPurpose}}</td>
          </tr>
          <tr>
            <th>Approver Status</th>
            <td width="20">:</td>
            <td><label>{{$result['approver_status']}}</label></td>
          </tr>
          <tr>
            <th>Finance Status</th>
            <td width="20">:</td>
            <td><label>{{$data->Status}}</label></td>
          </tr>
          <tr>
            <th>Total Amount</th>
            <td width="20">:</td>
            <td><span class="amount text-primary"><b>{{ $totalValue }} INR</b></span></td>
          </tr>
          <tr>
            <th>Advance Amount</th>
            <td width="20">:</td>
            <td>@if($data->AdvanceAmount)
            {{$data->AdvanceAmount}}
        @else
            N/A
        @endif</td>
          </tr>
        </table>
      </div>
      
      <h4 class="sub-heading">Reporting Person Approval Section</h4>
      <div class="bg-cover">
        <table class="table">
          <tr>
            <th width="300">Reporting person name</th>
            <td width="20">:</td>
            <td>
            @if(isset($result['approver_details']['emp_id']) && isset($result['approver_details']['emp_name']))
                {{ $result['approver_details']['emp_id'] ?? 'N/A' }}/{{ $result['approver_details']['emp_name'] ?? 'N/A' }}
            @else
                N/A
            @endif
        </td>
          </tr>
          <tr>
            <th>Date of approval</th>
            <td width="20">:</td>
            <td>
              <?php 
                if (!empty($result['trip_approved_date'])) {
                  echo $result['trip_approved_date'];
                }else
                  echo 'NA';
              ?>
            </td>
          </tr>
          <tr>
            <th>Comments</th>
            <td width="20">:</td>
            <td><?php 
              if($data->ApproverRemarks)
                echo $data->ApproverRemarks;
              else
                echo '';
            ?></td>
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
          @php
    $totalDeduction = 0; // Initialize total deduction variable
    $totalcatAmont = 0; // Initialize total deduction variable
@endphp
            @foreach($result['categories'] as $category)
            @php
                $totalAmount = $category['claim_details']->sum(function($claimDetail) {
                    return $claimDetail['qty'] * $claimDetail['unit_amount'];
                });
                $catAmount = $category['claim_details']->sum(function($claimDetail) {
                    return $claimDetail['qty'] * $claimDetail['DeductAmount'];
                });
                $totalDeductAmount = $category['claim_details']->sum(function($claimDetail) {
                    return $claimDetail['DeductAmount'] - $claimDetail['unit_amount'];
                });
                $totalDeduction=$totalDeduction+$totalDeductAmount;
                $totalcatAmont=$totalcatAmont+$catAmount;
            @endphp
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $category['category_name'] }} </td>
              <td>{{ number_format($catAmount, 2) }} INR</td>
              <td>{{ number_format($totalAmount, 2) }} INR</td>
              <td>{{ number_format($totalDeductAmount, 2) }} INR</td>
            </tr>
            @endforeach  
            <tr style="font-weight: bold;">
              <td colspan="2">Total</td>
              <td>{{ $totalcatAmont }} INR</td>
              <td>{{ $totalValue }} INR</td>
              <td>{{$totalDeduction}} INR</td>
            </tr>
          </tbody>
        </table>
      </div>
     
      <!-- Finance Approval Section --->
      <h4 class="sub-heading">Finance user Approval Section</h4>
      <div class="bg-cover">
        <table class="table">
        <form action="{{ route('finance_approve_claim') }}" method="POST">
          <tr class="d-none">
            <th width="300">TripID</th>
            <td width="20">:</td>
            <td><b>{{$advanceBalance}} INR</b></td>
          </tr>
          <tr>
            <th width="300">Advance Paid</th>
            <td width="20">:</td>
            <td><b>{{$advanceBalance}} INR</b></td>
          </tr>
          
          <tr>
            <th>Amount to be settled</th>
            <td width="20">:</td>
            <td><b>{{ number_format($totalValue - $advanceBalance, 2) }} INR</b></td>
          </tr>
          <?php
          // dd($userdet);
          ?>
          <tr>
            <th>Approver ID</th>
            <td width="20">:</td>
           
          </tr>
          <tr>
            <th>Approver name</th>
            <td width="20">:</td>
            <td>{{$userdet->emp_name}}</td>
          </tr>
          <tr>
              <th>Date</th>
              <td width="20">:</td>
              <td>{{ now()->format('d/m/Y') }}</td>
          </tr>
          <tr>
            <th>Comments</th>
            <td width="20">:</td>
            <td>
              <textarea rows="2" name="remarks" class="form-control" value=""><?php echo $_GET['remarks']; ?></textarea>
              
            </td>
          </tr>
          <tr>
            <th>&nbsp;</th>
            <td width="20">&nbsp;</td>
            <td>
              <div class="button-group">
              
                <input type="hidden" name="TripClaimID" id="TripClaimID" value="{{$data->TripClaimID}}">
                <input type="hidden" name="SettleAmount" id="SettleAmount" value="{{ number_format($totalValue - $advanceBalance, 2) }}">
                <input type="hidden" name="FinanceApproverID" id="FinanceApproverID" value="{{$userdet->emp_id}}">
                
                @csrf <!-- CSRF token for security -->
                <button type="submit" name="action" value="approve" class="btn btn-success">Approve</button>
                <button type="submit" name="action" value="reject"  class="btn btn-danger">Reject</button>
              
                
               
              </div>
            </td>
          </tr>
          </form>
        </table>
      </div> 
    </div>
  </div>
  <?php // dd($result);?>


  <!---------view Attachment----------->
  <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">View Attachment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img src="" alt="Image" style="margin-left:auto;margin-right:auto;width:100%;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a id="downloadLink" href="#" class="btn btn-info" download>
                    <i class="fa fa-download" aria-hidden="true"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>
 <!---------view Attachment end----------->
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


<div class="modal fade myg-modal" id="updateModal">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Update Amount</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form method="POST">
      <!-- Modal body -->
      <div class="modal-body">      
        <div class="row">
        @csrf
          <div class="col-5">Enter new amount</div>
          <div class="col-1">:</div>
          <div class="col-6"><input type="number" class="form-control" id="updateamount" name="UnitAmount" required> </div>
          <input type="hidden" value="" name="TripClaimDetailID" id="TripClaimDetailID" >   
        </div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="reset" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger" id="updateAmountSubmit" data-dismiss="modal">Update</button>
      </div>
      </form>
    </div>
  </div>
</div>
<script>
  $(document).ready(function(){
    
        // Get the TripClaimDetailID from the URL
        const urlParams = new URLSearchParams(window.location.search);
        const TripClaimDetailID = urlParams.get('TripClaimDetailID');

        if (TripClaimDetailID) {
            // Scroll to the element and expand the section
            var elementId = '#amount_' + TripClaimDetailID;
            var collapseSection = $(elementId).closest('.collapse');

            // Check if the element is within a collapsible section and expand it if necessary
            if (collapseSection.length && !collapseSection.hasClass('show')) {
                collapseSection.collapse('show');
            }

            // Scroll to the amount row
            $('html, body').animate({
                scrollTop: $(elementId).offset().top - 100 // Adjust for header/navbar height
            }, 1000);
        }
    $("#submit_btn").on("click",function(){
      $('#confirmModal').modal('show');
    });
    // Allow only digits and a single decimal point
    $('#updateamount').on('keypress', function (event) {
        var charCode = (event.which) ? event.which : event.keyCode;
        var currentValue = $(this).val();

        // Allow only digits (0-9) and one decimal point
        if ((charCode != 46 || currentValue.indexOf('.') != -1) && (charCode < 48 || charCode > 57)) {
            event.preventDefault();
        }
    });

    // Optionally, limit the number of decimal places to 2
    $('#updateamount').on('input', function (event) {
        var value = $(this).val();
        // Check if there's a decimal point and limit to 2 decimal places
        if (value.indexOf('.') != -1) {
            var parts = value.split('.');
            if (parts[1].length > 2) {
                $(this).val(parts[0] + '.' + parts[1].substring(0, 2));
            }
        }
    });
    $('.view-image').on('click', function() {
        var imgSrc = $(this).data('imgsrc');
        $('#imageModal').find('img').attr('src', imgSrc);
        $('#downloadLink').attr('href', imgSrc);
    });
   
    $('#approveButton, #rejectButton').on('click', function() {
        var action = $(this).data('action'); // Get the action value (approve or reject)
        var form = $('#financeApproveForm');
        
        $.ajax({
            url: form.attr('action'), // Get the form action URL
            type: 'POST',
            data: form.serialize() + '&action=' + action, // Serialize form data and append action
            success: function(response) {
                alert('Form submitted successfully!');
            },
            error: function(xhr) {
                // Handle any errors that occur
                alert('An error occurred: ' + xhr.responseText);
            }
        });
    });
    $("#updateAmountSubmit").on("click",function(){
      var TripClaimDetailID= $('#TripClaimDetailID').val();
      var UnitAmount= $('#updateamount').val();
      if(TripClaimDetailID!=""&& UnitAmount!=""){
        $.ajax({
          type:'POST',
          url:'{{url("/update_tripclaimDetails")}}',
          data: {
              "_token": "{{ csrf_token() }}",
              "TripClaimDetailID": TripClaimDetailID,
              "UnitAmount": UnitAmount,
          },
          success:function(data) {
              Swal.fire({
                  title: "Success!",
                  text: "Amount updated successfully",
                  icon: "success",
              }).then(function() {
                window.location.href = window.location.origin + window.location.pathname + '?TripClaimDetailID=' + TripClaimDetailID 
              });
          }
      });
      }
    })
  });

  function updateModel(id){
    $('#TripClaimDetailID').val(id);
    $('#updateamount').val("");
    $('#updateModal').modal('show');
  }
  function rejectModel(id,trip_id){
    Swal.fire({
        title: 'Are you sure?',
        text: "Are you sure you want to reject this category?",
        icon: 'warning',
        showCancelButton: true, // Show the cancel button
        confirmButtonText: 'Yes, Reject it!',
        cancelButtonText: 'Cancel',
        dangerMode: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type:'POST',
                url:'{{url("/reject_tripclaimDetails")}}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "TripClaimDetailID": id,
                    "TripClaimID": trip_id,
                },
                success:function(data) {
                    Swal.fire({
                        title: "Success!",
                        text: "Category has been rejected!..",
                        icon: "success",
                    }).then(function() {
                      if(data.approvedCount!=0){

                        window.location.href = "{{url('ro_approved_claims_view/')}}/" + trip_id;
                      }else{
                        window.location.href = "{{url('ro_approved_claims')}}";
                      }
                      // $("#submit_btn").hide();
                    });
                }
            });
        }
    }).then((willCancel) => {
        if (willCancel) {
            window.location.href = "{{url("ro_approved_claims")}}";
        }
    }); 
  }
</script>
@include("admin.include.footer")

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
<?php
//  dd($result);
 ?>
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
          
          <!--<tr>
            <th>Branch Name</th>
            <td width="20">:</td>
            <td>@if($result['user_details'] && $result['user_details']['emp_branch'])
            {{$result['user_details']['emp_branch']}}
              @else
                  N/A
              @endif</td>
          </tr>-->
         <tr>
            <th>Type of Trip</th>
            <td width="20">:</td>
            <td>{{$data->triptypedetails->TripTypeName}}</td>
          </tr>
          <tr>
            <th>Visited Branch</th>
            <td width="20">:</td>
            <td>{{ $result['visit_branch_detail']['branch_name'] ?? 'NA' }}</td>
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
        <div id="accordion">
          <div class="card">

          @foreach($result['categories'] as $category)
    @php
        $totalAmount = $category['claim_details']->sum(function($claimDetail) {
            return $claimDetail['qty'] * $claimDetail['unit_amount'];
        });
    @endphp

    <div class="card-header" id="headingOne">
        <h5 class="mb-0">
            <button class="btn btn-link" data-toggle="collapse" data-target="#categories{{ $loop->index }}" aria-expanded="true" aria-controls="categories{{ $loop->index }}">
                <span>{{ $category['category_name'] }} Expenses</span>
                <b>Total Amount: {{ number_format($totalAmount, 2) }} INR</b>
                <i class="fa fa-angle-down" aria-hidden="true"></i>
            </button>
        </h5>
    </div>

    <div id="categories{{ $loop->index }}" class="collapse show" aria-labelledby="headingOne">
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <th width="20">Sl.</th>
                    @if($category['document_date_flag'])<th>Date</th>@endif
                    @if($category['trip_from_flag'])<th>From</th>@endif
                    @if($category['trip_to_flag'])<th>To</th>@endif
                    @if($category['from_date_flag'])<th>From Date</th>@endif
                    @if($category['to_date_flag'])<th>To Date</th>@endif
                    <th>Policy class</th>
		@if($category['end_meter_flag'])<th>Total Km</th>@endif
                    <th width="50">No. of employees</th>
                    <th width="150">Employee IDs/Name</th>
                    <th width="200">Remarks</th>
                    <th width="200" style="width:160px;">Attached file</th>
                    <th>Amount</th>
                    <th>Action</th>
                </thead>
                <tbody>
                @foreach($category['claim_details'] as $claimdetails)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        @if($category['document_date_flag'])
                            <td>{{ \Carbon\Carbon::parse($claimdetails['document_date'])->format('d-m-Y') }}</td>
                        @endif
                        @if($category['trip_from_flag'])
                            <td>{{ $claimdetails['trip_from'] }}</td>
                        @endif
                        @if($category['trip_to_flag'])
                            <td>{{ $claimdetails['trip_to'] }}</td>
                        @endif
                        @if($category['from_date_flag'])
                            <td>{{ \Carbon\Carbon::parse($claimdetails['from_date'])->format('d-m-Y') }}</td>
                        @endif
                        @if($category['to_date_flag'])
                            <td>{{ \Carbon\Carbon::parse($claimdetails['to_date'])->format('d-m-Y') }}</td>
                        @endif
                        <td>
                        @if($claimdetails['policy_details']['grade_type']==='Class')
                          <span class="badge badge-primary">{{ $claimdetails['policy_details']['sub_category_name'] }}</span>
                        @else
                            @if(!empty($claimdetails['person_details']))
                                @foreach($claimdetails['person_details'] as $person)
                                    @if($person)
                                    
                                        <span class="badge badge-primary">{{ $person['user_policy'] }}</span><br>
                                    @endif
                                @endforeach
                            @else
                                <span>NA</span>
                            @endif

                        @endif
                           
                        </td>
 			@if($category['end_meter_flag'])
                            <td>{{ $claimdetails['end_meter']}}</td>
                        @endif
                        <td>{{ $claimdetails['no_of_persons'] }}</td>
                        {{-- <td>
                            @if(!empty($claimdetails['person_details']))
                                @foreach($claimdetails['person_details'] as $person)
                                    @if($person)
                                        <span class="badge badge-dark">{{ $person['emp_name'] }}/{{ $person['emp_id'] }}</span><br>
                                       <?php if($claimdetails['person_details']["is_duplication"]=== true){
                                        echo "<a href={{ url('ro_approved_claims_view/') }}>View Duplicate Claim</a>";
                                       }?>
                                    @endif
                                @endforeach
                            @else
                                <span>No Person Details Available</span>
                            @endif
                        </td> --}}
                        <td>
                          @if(!empty($claimdetails['person_details']))
                              @foreach($claimdetails['person_details'] as $person)
                                  @if($person)
                                      <span class="badge badge-dark">{{ $person['emp_name'] }}/{{ $person['emp_id'] }}</span><br>
                                  @endif
                              @endforeach
                      
                              @if(!empty($claimdetails['is_duplication']) && $claimdetails['is_duplication'] === true)
                                  {{-- <a href="{{ url('ro_approved_claims_view/') }}">View Duplicate Claim</a> --}}
                              @endif 
                          @else
                              <span>No Person Details Available</span>
                          @endif
                      </td>
                        <td><small>{{ $claimdetails['remarks'] }}</small></td>
                        <td class="text-center">
                       
@if(!empty($claimdetails['file_url']))
    <a href="javascript:void(0);" class="btn btn-primary view-image" data-toggle="modal" data-target="#imageModal" data-imgsrc="{{ route('filesview.view', ['filename' => basename($claimdetails['file_url'])]) }}">
        <i class="fa fa-eye" aria-hidden="true"></i>
    </a>

    <a href="{{ route('filesview.view', ['filename' => basename($claimdetails['file_url'])]) }}" download class="btn btn-info">
        <i class="fa fa-download" aria-hidden="true"></i>
    </a>
@endif

                        </td>
                        <td class="text-center"><span class="value" id="amount_{{ $claimdetails["trip_claim_details_id"] }}">{{ number_format($claimdetails['unit_amount'], 2) }}</span>
                        @if($category['class_flg'] == 1)
                        <u><label onclick="updateModel('{{ $claimdetails["trip_claim_details_id"] }}')"  style="cursor: pointer;">Update</label></u>
                        @endif
                        @if($result['approver_details']['emp_id'] != $claimdetails['approver_id'])
                        <span class="value"><i class="fa fa-check-circle text-success" aria-hidden="true"></i></span>
                        <?php
                        echo '<a href="#" class="btn btn-info btn-view" onclick="viewSpecialApprovalModal(\'TMG' . substr($data->TripClaimID, 8) . '\',\'' . addslashes($data->userdata->emp_id) . '/' . addslashes($data->userdata->emp_name) . '\',\'' . addslashes($data->triptypedetails->TripTypeName) . '\',\'' . addslashes($category['category_name']) . '\',\'' . addslashes($claimdetails['remarks']) . '\',\'' . number_format($claimdetails['unit_amount'], 2) . ' INR\',\'' . addslashes($result['approver_details']['emp_id']) . '/' . addslashes($result['approver_details']['emp_name']) . '\',\'' . addslashes($claimdetails['approver_id']) .'/'. addslashes($claimdetails['claim_approver_name']).'\',\'' . addslashes($claimdetails['approver_remarks']) . '\',\'' . number_format($claimdetails['policy_details']['grade_amount'], 2) . ' INR\',\'' . number_format(($claimdetails['unit_amount']-$claimdetails['policy_details']['grade_amount']), 2). ' INR\')">View</a>';
                    
                        ?>
                        @endif
                        </td>
                        <td>
                        <button type="button" class="btn btn-danger" onclick="rejectModel('{{ $claimdetails["trip_claim_details_id"] }}','{{ $result["trip_claim_id"] }}');">Reject</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
           
            <h4 class="sub-heading mt-4">Attendance Details</h4>
            <table class="table table-striped table-bordered">
                  <thead>
                    <tr>
                      <th width="20">Sl.</th>
                      <th>Date</th>
                      <th>Employee ID</th>
                      <th>Punch In</th>
                      <th>Location In</th>
                      <th>Punch Out</th>
                      <th>Location Out</th>
                      <th>Duration</th>
                      <th>Remarks</th>
                    </tr>
                  </thead>
                  <tbody>
                      @php
                          // Sort the attendance details by date and emp_id
                          $sortedAttendanceDetails = collect($category['attendance_details'])
                              ->sort(function ($a, $b) {
                                  // Sort by date first
                                  $dateComparison = strcmp($a['date'], $b['date']);
                                  if ($dateComparison === 0) {
                                      // If dates are the same, sort by emp_id
                                      return strcmp($a['emp_id'], $b['emp_id']);
                                  }
                                  return $dateComparison;
                              });
                      @endphp
                      @if ($sortedAttendanceDetails->isEmpty())
                          <tr>
                              <td colspan="9" class="text-center">No data found</td>
                          </tr>
                      @else
                        @foreach($sortedAttendanceDetails as $attendance)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ \Carbon\Carbon::parse($attendance['date'])->format('d-m-Y') }}</td>
                          <td>{{ $attendance['emp_id'] }}</td>
                          <td>{{ $attendance['punch_in'] ? \Carbon\Carbon::parse($attendance['punch_in'])->format('H:i') : '' }}</td>
                          <td>{{ $attendance['location_in'] }}</td>
                          <td>{{ $attendance['punch_out'] ? \Carbon\Carbon::parse($attendance['punch_out'])->format('H:i') : '' }}</td>

                          <td>{{ $attendance['location_out'] }}</td>
                          <td>{{ $attendance['duration'] }}</td>
                          <td>{{ $attendance['remarks'] }}</td>
                        </tr>
                        @endforeach
                      @endif
                  </tbody>
                </table>

        </div>
    </div>
@endforeach

         

          </div>

          

        </div>
      </div>
     
      <!-- Finance Approval Section --->
      <h4 class="sub-heading">Finance user Approval Section</h4>
      <div class="bg-cover approver-section bg-grey">
        <table class="table">
        <form action="{{ route('ro_approved_claims_approved_view', ['id' => $data->TripClaimID]) }}" method="GET">
          <?php //finance_approve_claim ?>
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
              <textarea rows="2" name="remarks" id="fremarks" class="form-control"></textarea>
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
                <!-- <button type="submit" name="action" value="approve" id="hiddenApprove" class="btn btn-success d-none">Approve</button>
                <button type="submit" name="action" value="reject" id="hiddenReject" class="btn btn-danger d-none">Reject</button>
               -->
                
                <button type="submit" value="" id="submit_btn" class="btn btn-primary" 
                  @if($result['RejectedCount'] > 0) disabled @endif>
                  Submit
              </button>
              </div>
            </td>
          </tr>
          </form>
        </table>
      </div> 
    </div>
  </div>



  <!---------view Attachment----------->
  <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
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
        <h4>Special approval request for <span id="categoryName" class="text-primary"></span></h4>
        <table class="table">
          <tr>
            <th width="30%">Trip ID</th>
            <td width="5%">:</td>
            <td width="65%" id="tripClaimID"></td>
          </tr>
          <tr>
            <th>Employee ID/name</th>
            <td>:</td>
            <td id="empDetails"></td>
          </tr>
          <tr>
            <th>Type of trip</th>
            <td>:</td>
            <td id="tripType"></td>
          </tr>
          <tr>
            <th>Category</th>
            <td>:</td>
            <td id="categoryDetails"></td>
          </tr>
          <tr>
            <th>Remarks</th>
            <td>:</td>
            <td id="remarks"></td>
          </tr>
          <tr>
            <th>Total amount</th>
            <td>:</td>
            <td id="unitAmount"></td>
          </tr>
        </table>
        <hr>
        <h4>Reporting person section</h4>
        <table class="table">
          <tr>
            <th width="30%">Special approval requested by</th>
            <td width="5%">:</td>
            <td width="65%" id="approverDetails"></td>
          </tr>
                  
        </table>
        <hr>
        <h4>Special approver section</h4>
        <table class="table">
          <tr>
            <th width="30%">Special approver name</th>
            <td width="5%">:</td>
            <td width="65%" id="specialApprover">Adham(MYGX-0000)</td>
          </tr>
          <tr>
            <th>Remarks</th>
            <td>:</td>
            <td id="approverRemarks"></td>
          </tr>
          <tr>
            <th>Eligible amount</th>
            <td>:</td>
            <td><span class="text-primary"><b id="Eligible"></b></span></td>
          </tr>
          <tr>
            <th>Additional amount</th>
            <td>:</td>
            <td><span class="text-primary"><b id="Additional"></b></span></td>
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
          <div class="col-5">Enter Remarks</div>
          <div class="col-1">:</div>
          <div class="col-6"><textarea class="form-control" id="updateremarks" name="remarks"></textarea></div>
           
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
  function viewSpecialApprovalModal(tripClaimID, empDetails, tripType, categoryName, remarks, unitAmount, approverDetails,specialApprover,approverRemarks,Eligible,Additional) {
    // Show the modal
      // Populate modal fields with the passed values
      $('#tripClaimID').text(tripClaimID); // Trip Claim ID
      $('#empDetails').text(empDetails);   // Employee ID/name
      $('#tripType').text(tripType);       // Type of trip
      $('#categoryName').text(categoryName); // Category name in the heading
      $('#categoryDetails').text(categoryName); // Category name in the table
      $('#remarks').text(remarks);         // Remarks
      $('#unitAmount').text(unitAmount);   // Total amount
      $('#approverDetails').text(approverDetails); // Approver ID/name
      $('#approverRemarks').text(approverRemarks);
      $('#specialApprover').text(specialApprover);
      $('#Eligible').text(Eligible);
      $('#Additional').text(Additional)
      $('#viewSpecialApprovalModal').modal('show');;

  }
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
        var remarks = $('#fremarks').text();
        
        $.ajax({
            url: form.attr('action'), // Get the form action URL
            type: 'POST',
            data: form.serialize() + '&action=' + action+'&remarks='+remarks, // Serialize form data and append action
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
      var Remarks= $('#updateremarks').val();
      if(TripClaimDetailID!=""&& UnitAmount!=""){
        $.ajax({
          type:'POST',
          url:'{{url("/update_tripclaimDetails")}}',
          data: {
              "_token": "{{ csrf_token() }}",
              "TripClaimDetailID": TripClaimDetailID,
              "UnitAmount": UnitAmount,
              "approver_remarks": Remarks,
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
  function rejectModel(id, trip_id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Please provide a reason for rejecting this category.",
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Enter your reason here...',
        inputAttributes: {
            'aria-label': 'Enter your reason here...'
        },
        showCancelButton: true,
        confirmButtonText: 'Yes, Reject it!',
        cancelButtonText: 'Cancel',
        dangerMode: true
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value.trim() === '') {
                Swal.fire({
                    title: 'Error',
                    text: 'Please provide a reason for rejecting.',
                    icon: 'error',
                });
                return;
            }

            $.ajax({
                type: 'POST',
                url: '{{ url("/reject_tripclaimDetails") }}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "TripClaimDetailID": id,
                    "TripClaimID": trip_id,
                    "reason": result.value // Include the reason in the data sent to the server
                },
                success: function(data) {
                    Swal.fire({
                        title: "Success!",
                        text: "Category has been rejected!",
                        icon: "success",
                    }).then(function() {
                        if (data.approvedCount != 0) {
                            window.location.href = "{{ url('ro_approved_claims_view/') }}/" + trip_id;
                        } else {
                            window.location.href = "{{ url('ro_approved_claims') }}";
                        }
                    });
                }
            });
        }
    }).catch((error) => {
        if (error) {
            window.location.href = "{{ url('ro_approved_claims') }}";
        }
    });
}
</script>
@include("admin.include.footer")

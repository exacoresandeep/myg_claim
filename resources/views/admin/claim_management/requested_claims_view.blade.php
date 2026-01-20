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
        <a href="{{ url('claim_request')}}">
          <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Back
        </a>
      </div>
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
            <td>
              @if (!empty($result['visit_branch_detail']))
                  {{ collect($result['visit_branch_detail'])->map(fn($b) => $b['branch_name'] . ' (' . $b['branch_code'] . ')')->implode(', ') }}
              @else
                  NA
              @endif
          </td>

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
                if (!empty($result->trip_approved_date)) {
                  echo date('d-m-Y', strtotime($result->trip_approved_date));
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
<?php 
// dd($result);  
 ?>
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

    <div id="categories{{ $loop->index }}" class="collapse" aria-labelledby="headingOne">
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
                            <td>{{ $claimdetails['end_meter'] }}</td>
                        @endif
                        <td>{{ $claimdetails['no_of_persons'] }}</td>
                        <td>
                            @if(!empty($claimdetails['person_details']))
                                @foreach($claimdetails['person_details'] as $person)
                                    @if($person)
                                        <span class="badge badge-dark">{{ $person['emp_name'] }}/{{ $person['emp_id'] }}</span><br>
                                    @endif
                                @endforeach
                            @else
                                <span>No Person Details Available</span>
                            @endif
                        </td>
                        <td><small>{{ $claimdetails['remarks'] }}</small></td>
                        <td >
                        @if(!empty($claimdetails['file_url']))
    <a href="javascript:void(0);" class="btn btn-primary view-image" data-toggle="modal" data-target="#imageModal" data-imgsrc="{{ route('filesview.view', ['filename' => basename($claimdetails['file_url'])]) }}">
        <i class="fa fa-eye" aria-hidden="true"></i>
    </a>

    <a href="{{ route('filesview.view', ['filename' => basename($claimdetails['file_url'])]) }}" download class="btn btn-info">
        <i class="fa fa-download" aria-hidden="true"></i>
    </a>
@endif
                        </td>
                        <td><span class="value">{{ number_format($claimdetails['unit_amount'], 2) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

         

          </div>

          

        </div>
      </div>
     
      <!-- Finance Approval Section --->
      <!-- <h4 class="sub-heading">Finance user Approval Section</h4>
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
      </div> -->
    </div>
  </div>
  <?php // dd($result);?>


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
<script>
  $(document).ready(function(){
    $('.view-image').on('click', function() {
        var imgSrc = $(this).data('imgsrc');
        $('#imageModal').find('img').attr('src', imgSrc);
        $('#downloadLink').attr('href', imgSrc);
    });
  })
</script>
@include("admin.include.footer")

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>myG :: myGourney Application</title>
@include("admin.include.header")

<!-- <div class="main-content"> -->
@include("admin.include.sidebar-menu")
  <div class="main-area">
    <h2 class="main-heading">Hello, Welcome to myGourney Dashboard</h2>
    
     <div class="dashbox-cover">
      <div class="dashbox-in">
      <a href="{{route('claim_request')}}">
        <div class="dashbox">
          <i class="fa fa-files-o" aria-hidden="true"></i>
          <h4>{{ $totalClaims }}</h4>
        </div>
        <h3>Claim Requests</h3>
        </a>
      </div>
      <div class="dashbox-in">
        <a href="{{route('ro_approved_claims')}}">
          <div class="dashbox">
            <i class="fa fa-check-circle" aria-hidden="true"></i>
            <h4>{{ $pendingCount }}</h4>
          </div>
        <h3>RO Approved</h3>
        </a>
      </div>

      <div class="dashbox-in">
      <a href="{{route('approved_claims')}}">
        <div class="dashbox">
          <i class="fa fa-check-square" aria-hidden="true"></i>
          <h4>{{ $approvedCount }}</h4>
        </div>
        <h3>Finance Approved</h3>
        </a>
      </div>
      <div class="dashbox-in">
        <a href="{{route('settled_claims')}}">
          <div class="dashbox">
            <i class="fa fa-chain" aria-hidden="true"></i>
            <h4>{{ $settledCount }}</h4>
          </div>
          <h3>Claim Settled</h3>
          </a>
        </div>
    </div>

    <div class="dashbox-cover">
      <div class="dashbox-in">
      <a href="{{route('claim_request')}}">
        <div class="dashbox"> 
          <i class="fa fa-money" aria-hidden="true"></i>
          <h3>{{ $totalClaimsAmount }}</h3>
        </div>
        <p style="font-size:11px;"  class="mt-1">Total Requests Amount</p>
        </a>
      </div>
      <div class="dashbox-in">
        <a href="{{route('ro_approved_claims')}}">
          <div class="dashbox"> 
            <i class="fa fa-money" aria-hidden="true"></i>
            <h3>{{ $pendingAmount }}</h3>
          </div>
          <p style="font-size:11px;"  class="mt-1">Total RO Approved Amount</p>
        </a>
      </div>
      
      <div class="dashbox-in">
        <a href="{{route('approved_claims')}}">
          <div class="dashbox"> 
            <i class="fa fa-money" aria-hidden="true"></i>
            <h3>{{ $approvedAmount }}</h3>
          </div>
          <p style="font-size:11px;"  class="mt-1">Total Finance Approved Amount</p>
          
        </a>
      </div> 
      <div class="dashbox-in">
        <a href="{{route('settled_claims')}}">
          <div class="dashbox"> 
            <i class="fa fa-money" aria-hidden="true"></i>
            <h3>{{ $settledAmount }}</h3>
          </div>
          <p style="font-size:11px;"  class="mt-1">Total Settled Amount</p>
        </a>
      </div>     
    </div>
    <div class="dash-other">
      
    </div>
  </div>
<!-- </div> -->

@include("admin.include.footer")

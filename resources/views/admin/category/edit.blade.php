<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>All Categories</title>
    @include("admin.include.header")
</head>
<body>
    @include("admin.include.sidebar-menu")
    <div class="main-area">
    <div class="back-btn" id="back-button">
        <a href="{{ url('claim_category') }}">
          <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Back
        </a>
      </div>
        <h2 class="main-heading">Edit Category</h2>
        <div class="dash-all pt-0">
            <div class="dash-table-all" style="max-width:700px;">
                <form method="POST" action="{{ url('update_category_submit') }}" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="{{$Category->CategoryID}}">
                    @csrf

                    <table class="table table-striped">
                       
                        <tr>
                            <td>Category Name<span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <input type="text" class="form-control @error('CategoryName') is-invalid @enderror" name="CategoryName" required autocomplete="off" value="{{$Category->CategoryName}}">
                                @error('CategoryName')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                
                        <td>Trip From</td>
                        <td width="10">:</td>

                        <td><input type="hidden" name="TripFrom" value="0"><input type="checkbox" name="TripFrom" value="1" class="" {{ $Category->TripFrom ? 'checked' : '' }}></td>
                    </tr>
                    <tr>
                        
                        <td>Trip To</td>
                        <td width="10">:</td>
                        <td><input type="hidden" name="TripTo" value="0"><input type="checkbox" name="TripTo" value="1" class="" {{ $Category->TripTo ? 'checked' : '' }}></td>
                    </tr>
                    <tr>
                        
                        <td>From Date</td>
                        <td width="10">:</td>
                        <td><input type="hidden" name="FromDate" value="0"><input type="checkbox" name="FromDate" value="1" class="" {{ $Category->FromDate ? 'checked' : '' }}></td>
                    </tr>
                    <tr>
                        
                        <td>To Date</td>
                        <td width="10">:</td>
                        <td><input type="hidden" name="ToDate" value="0"><input type="checkbox" name="ToDate" value="1" class="" {{ $Category->ToDate ? 'checked' : '' }}></td>
                    </tr>

                    <tr>
                        
                        <td>Document Date</td>
                        <td width="10">:</td>
                        <td><input type="hidden" name="DocumentDate" value="0"><input type="checkbox" name="DocumentDate" value="1"  {{ $Category->DocumentDate ? 'checked' : '' }} class=""></td>
                    </tr>
                    <tr>
                        
                        <td>Start Meter</td>
                        <td width="10">:</td>
                        <td><input type="hidden" name="StartMeter" value="0"><input type="checkbox" name="StartMeter" value="1" {{ $Category->StartMeter ? 'checked' : '' }} class=""></td>
                    </tr>
                    <tr>
                        
                        <td>End Meter</td>
                        <td width="10">:</td>
                        <td><input type="hidden" name="EndMeter" value="0"><input type="checkbox" name="EndMeter" value="1" {{ $Category->EndMeter ? 'checked' : '' }} class=""></td>
                    </tr>
                    <tr>
                            <td>Icon Image</td>
                            <td width="10">:</td>
                            <td>
                                @if($Category->ImageUrl)
                                    <img src="{{ asset('images/category/' . $Category->ImageUrl) }}" alt="Current Icon Image" style="max-width: 100px; margin-bottom: 10px;">
                                @endif
                                <input type="file" class="form-control-file @error('ImageUrl') is-invalid @enderror" name="ImageUrl">
                                @error('ImageUrl')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <small class="form-text text-muted">Leave this field empty if you don't want to change the icon image.</small>
                            </td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td width="10">:</td>
                            <td>
                                <select class="form-control" name="Status" id="Status">
                                    <option value="">Select</option>
                                    <option value="1" {{ $Category->Status == "1" ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $Category->Status == "0" ? 'selected' : '' }}>InActive</option>
                                    <option value="2" {{ $Category->Status == "2" ? 'selected' : '' }}>Deleted</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <button class="button_orange">Update</button>
                </form>
            </div>
        </div>
    </div>

    @include("admin.include.footer")
</body>
</html>

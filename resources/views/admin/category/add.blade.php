<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Category </title>
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
      <h2 class="main-heading">Add Category</h2>  
      <div class="dash-all pt-0">
        <div class="dash-table-all" style="max-width:700px;">  
          <form method="POST" action="{{ url('add_category_submit') }}" enctype="multipart/form-data">
            @csrf   
            <table class="table table-striped">        
             
              <tr>
                <td>Category name<span style="color: red;">*</span></td>
                <td width="10">:</td>
                <td>
                  <input type="text" class="form-control @error('CategoryName') is-invalid @enderror" name="CategoryName" value="{{ old('CategoryName') }}" required autocomplete="off">
                  @error('CategoryName')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                  @enderror  
                </td></tr><tr>
                
                  <td>Trip From</td>
                  <td width="10">:</td>

                  <td><input type="hidden" name="TripFrom" value="0"><input type="checkbox" name="TripFrom" value="1" class=""></td>
              </tr>
              <tr>
                
                  <td>Trip To</td>
                  <td width="10">:</td>
                  <td><input type="hidden" name="TripTo" value="0"><input type="checkbox" name="TripTo" value="1" class=""></td>
              </tr>
              <tr>
                
                  <td>From Date</td>
                  <td width="10">:</td>
                  <td><input type="hidden" name="FromDate" value="0"><input type="checkbox" name="FromDate" value="1" class=""></td>
              </tr>
              <tr>
                
                  <td>To Date</td>
                  <td width="10">:</td>
                  <td><input type="hidden" name="ToDate" value="0"><input type="checkbox" name="ToDate" value="1" class=""></td>
              </tr>

              <tr>
                
                  <td>Document Date</td>
                  <td width="10">:</td>
                  <td><input type="hidden" name="DocumentDate" value="0"><input type="checkbox" name="DocumentDate" value="1" class=""></td>
              </tr>
              <tr>
                
                  <td>Start Meter</td>
                  <td width="10">:</td>
                  <td><input type="hidden" name="StartMeter" value="0"><input type="checkbox" name="StartMeter" value="1" class=""></td>
              </tr>
              <tr>
                
                  <td>End Meter</td>
                  <td width="10">:</td>
                  <td><input type="hidden" name="EndMeter" value="0"><input type="checkbox" name="EndMeter" value="1" class=""></td>
              </tr>
              <tr>
                <td>Category Icon (PNG)</td>
                <td width="10">:</td>
                <td>
                    <input type="file" class="form-control @error('ImageUrl') is-invalid @enderror" name="ImageUrl" accept="image/png" required>
                    @error('ImageUrl')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror  
                </td>
            </tr>
            </table>
            <button class="button_orange">Submit</button>
          </form>
        </div>
      </div>
    </div>

    @include("admin.include.footer")
  </body>
</html>

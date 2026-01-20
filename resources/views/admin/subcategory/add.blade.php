<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Sub-Category </title>
    @include("admin.include.header")
  </head>
  <body>
    @include("admin.include.sidebar-menu")

    <div class="main-area">
    <div class="back-btn" id="back-button">
        <a href="{{ url('sub_claim_category') }}">
          <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Back
        </a>
      </div>
      <h2 class="main-heading">Add Sub-Category</h2>  
      <div class="dash-all pt-0">
        <div class="dash-table-all" style="max-width:700px;">  
          <form method="POST" action="{{ url('add_subcategory_submit') }}">
            @csrf   
            <table class="table table-striped">        
             <!-- <tr>
                <td>UomID<span style="color: red;">*</span></td>
                <td width="10">:</td>
                <td>
                  <input type="text" class="form-control @error('UomID') is-invalid @enderror" name="UomID" value="{{ old('UomID') }}" required autocomplete="off">
                  @error('UomID')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                  @enderror  
                </td></tr> -->
               <td>Category</td>
                <td width="10">:</td>
                <td>
                    
                  <select class="form-control" name="Category" required>
                    <option value="">Select</option>
                    @foreach($category as $data)
                    <option value="{{$data->CategoryID}}">{{$data->CategoryName}}</option>
                    @endforeach
                  </select>
                </td>
                </tr> 
              <tr>
                <td>Sub-Category name<span style="color: red;">*</span></td>
                <td width="10">:</td>
                <td>
                  <input type="text" class="form-control @error('SubCategoryName') is-invalid @enderror" name="SubCategoryName" value="{{ old('SubCategoryName') }}" required autocomplete="off">
                  @error('SubCategoryName')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                  @enderror  
                </td></tr>
            </table>
            <button class="button_orange">Submit</button>
          </form>
        </div>
      </div>
    </div>

    @include("admin.include.footer")
  </body>
</html>

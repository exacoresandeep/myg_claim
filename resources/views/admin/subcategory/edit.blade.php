<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>All Sub-Categorys</title>
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
        <h2 class="main-heading">Edit Sub-Category</h2>
        <div class="dash-all pt-0">
            <div class="dash-table-all" style="max-width:700px;">
                <form method="POST" action="{{ url('update_subcategory_submit') }}">
                    <input type="hidden" name="id" value="{{$subcategory->SubCategoryID}}">
                    @csrf

                    <table class="table table-striped">
                        <tr>
                            <td>UomID<span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <input type="text" class="form-control @error('UomID') is-invalid @enderror" name="UomID" value="{{$subcategory->UomID}}" required autocomplete="off">
                                @error('UomID')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror  
                            </td>
                        </tr>
                        <tr>
                            <td>Category</td>
                            <td width="10">:</td>
                            <td>
                                <select class="form-control" name="Category">
                                    <option value="">Select</option>
                                    @foreach($category as $data)
                                        <option value="{{$data->CategoryID}}" 
                                            {{ $subcategory->CategoryID == $data->CategoryID ? 'selected' : '' }}>
                                            {{$data->CategoryName}}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr> 
                        <tr>
                            <td>Sub-Category name<span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <input type="text" class="form-control @error('SubCategoryName') is-invalid @enderror" name="SubCategoryName" value="{{$subcategory->SubCategoryName}}" required autocomplete="off">
                                @error('SubCategoryName')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror  
                            </td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td width="10">:</td>
                            <td>
                                <select class="form-control" name="Status" id="Status">
                                    <option value="">Select</option>
                                    <option value="1" {{ $subcategory->Status == "1" ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $subcategory->Status == "0" ? 'selected' : '' }}>InActive</option>
                                    <option value="2" {{ $subcategory->Status == "2" ? 'selected' : '' }}>Deleted</option>
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

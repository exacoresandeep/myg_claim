<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Policy </title>
    @include("admin.include.header")
  </head>
  <body>
    @include("admin.include.sidebar-menu")

    <div class="main-area">
    <div class="back-btn" id="back-button">
        <a href="{{ url('policy_management') }}">
          <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Back
        </a>
      </div>
      <h2 class="main-heading">Add Policy</h2>  
      <div class="dash-all pt-0">
        <div class="dash-table-all" style="max-width:700px;">  
          <form method="POST" action="add_policy_management_submit">
            @csrf   
            <table class="table table-striped">        
             
            <tr>
              <td>Grade<span style="color: red;">*</span></td>
              <td width="10">:</td>
              <td>
                  <select class="form-control @error('GradeID') is-invalid @enderror" name="GradeID" required>
                      <option value="">Select Grade</option>
                      @foreach($grades as $grade)
                          <option value="{{ $grade->GradeID }}" {{ old('GradeID') == $grade->GradeID ? 'selected' : '' }}>
                              {{ $grade->GradeName }}
                          </option>
                      @endforeach
                  </select>
                  @error('GradeID')
                      <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                  @enderror
              </td>
            </tr>
            <tr>
              <td>Category<span style="color: red;">*</span></td>
              <td width="10">:</td>
              <td>
                  <select class="form-control @error('CategoryID') is-invalid @enderror" name="CategoryID" id="CategoryID" required>
                      <option value="">Select Category</option>
                      @foreach($categories as $category)
                          <option value="{{ $category->CategoryID }}" {{ old('CategoryID') == $category->CategoryID ? 'selected' : '' }}>
                              {{ $category->CategoryName }}
                          </option>
                      @endforeach
                  </select>
                  @error('CategoryID')
                      <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                  @enderror
              </td>
            </tr>
            <tr>
              <td>SubCategory<span style="color: red;">*</span></td>
              <td width="10">:</td>
              <td>
                  <select class="form-control @error('SubCategoryID') is-invalid @enderror" name="SubCategoryID" id="SubCategoryID" required>
                      <option value="">Select SubCategory</option>
                      @foreach($subCategories as $subCategory)
                          <option value="{{ $subCategory->SubCategoryID }}" {{ old('SubCategoryID') == $subCategory->SubCategoryID ? 'selected' : '' }}>
                              {{ $subCategory->SubCategoryName }}
                          </option>
                      @endforeach
                  </select>
                  @error('SubCategoryID')
                      <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                  @enderror
              </td>
            </tr>
            <tr>
              <td>Policy Type<span style="color: red;">*</span></td>
              <td width="10">:</td>
              <td>
                  <select class="form-control @error('GradeType') is-invalid @enderror" name="GradeType" id="GradeType" required>
                      <option value="">Select Type</option>
                      <option value="Class">Class</option>
                      <option value="Amount">Amount</option>
                  </select>
                  @error('GradeType')
                      <span class="invalid-feedback" role="alert">
                          <strong>{{ $message }}</strong>
                      </span>
                  @enderror
              </td>
            </tr> 
            <tr id="ClassRow">
              <td>Class<span style="color: red;">*</span></td>
              <td width="10">:</td>
              <td>
                  <input type="text" class="form-control @error('GradeClass') is-invalid @enderror" name="GradeClass" id="GradeClass">
                  @error('GradeClass')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                  @enderror
              </td>
            </tr>
            <tr id="AmountRow">
              <td>Amount<span style="color: red;">*</span></td>
              <td width="10">:</td>
              <td>
                  <input type="number" class="form-control @error('GradeAmount') is-invalid @enderror" name="GradeAmount" id="GradeAmount">
                  @error('GradeAmount')
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
<script>
  $(document).ready(function(){
    @if(session()->has('error'))
        Swal.fire({
            title: "Warning!",
            text: "{{ session()->get('error') }}",
            icon: "warning",
        }).then(function() {
                // Reload DataTable after SweetAlert confirmation
                //$('#category-datatable').DataTable().ajax.reload();
            });
        @endif
    $('#ClassRow').hide();
    $('#AmountRow').hide();
    var hasGradeClassError = {{ $errors->has('GradeClass') ? 'true' : 'false' }};
    var hasGradeAmountError = {{ $errors->has('GradeAmount') ? 'true' : 'false' }};
    
    if (hasGradeClassError) {
        $('#ClassRow').show();
        $('#GradeType').val('Class');
    }

    if (hasGradeAmountError) {
        $('#AmountRow').show();
        $('#GradeType').val('Amount');
    }
    $("#CategoryID").on("change",function(){
      var category_id = $(this).val();
      console.log(category_id);
      if (category_id) {
        $.ajax({
            url: "{{ route('get-subcategories') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                category_id: category_id
            },
            success: function (data) {
                $('#SubCategoryID').empty();
                $('#SubCategoryID').append('<option value="">Select</option>');
                $.each(data, function (key, value) {
                    $('#SubCategoryID').append('<option value="' + value.SubCategoryID + '">' + value.SubCategoryName + '</option>');
                });
            }
        });
      } else {
        $('#SubCategoryID').empty();
        $('#SubCategoryID').append('<option value="">Select</option>');
      }
    });
    $('#GradeType').on('change', function() {
      var gradeType = $(this).val();
      if (gradeType === 'Class') {
        $('#ClassRow').show();
        $('#AmountRow').hide();
      } else if (gradeType === 'Amount') {
        $('#ClassRow').hide();
        $('#AmountRow').show();
      } else {
        $('#ClassRow').hide();
        $('#AmountRow').hide();
      }
    });

  });
</script>
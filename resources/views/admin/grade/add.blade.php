<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Grade :: DMS</title>
    @include("admin.include.header")
  </head>
  <body>
    @include("admin.include.sidebar-menu")

    <div class="main-area">
    <div class="back-btn" id="back-button">
        <a href="{{ url('grade') }}">
          <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Back
        </a>
      </div>
      <h2 class="main-heading">Add Grade</h2>  
      <div class="dash-all pt-0">
        <div class="dash-table-all" style="max-width:700px;">  
          <form method="POST" action="{{ url('add_grade_submit') }}">
            @csrf   
            <table class="table table-striped">        
             
              <tr>
                <td>Grade name<span style="color: red;">*</span></td>
                <td width="10">:</td>
                <td>
                  <input type="text" class="form-control @error('GradeName') is-invalid @enderror" name="GradeName" value="{{ old('GradeName') }}" required autocomplete="off">
                  @error('GradeName')
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

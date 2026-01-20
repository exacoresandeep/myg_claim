<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>All Grades :: DMS</title>
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
        <h2 class="main-heading">Edit Grade</h2>
        <div class="dash-all pt-0">
            <div class="dash-table-all" style="max-width:700px;">
                <form method="POST" action="{{ url('update_grade_submit') }}">
                    <input type="hidden" name="id" value="{{$grade->GradeID}}">
                    @csrf

                    <table class="table table-striped">
                       
                        <tr>
                            <td>Grade Name<span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <input type="text" class="form-control @error('GradeName') is-invalid @enderror" name="GradeName" required autocomplete="off" value="{{$grade->GradeName}}">
                                @error('GradeName')
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
                                    <option value="1" {{ $grade->Status == "1" ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $grade->Status == "0" ? 'selected' : '' }}>InActive</option>
                                    <option value="2" {{ $grade->Status == "2" ? 'selected' : '' }}>Deleted</option>
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

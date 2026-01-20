<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>All users :: DMS</title>
    @include("admin.include.header")
    <!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<style>
    .select2
    {width:100% !important;
    }
    .select2-container .select2-selection--single {
    height: 42px;
    font-size:16px;}
    .select2-selection{padding: 6px;}
    .select2-selection__arrow{
        margin-top: 5px;
    }
</style>
</head>
<body>
    @include("admin.include.sidebar-menu")
    <div class="main-area">
        <div class="back-btn" id="back-button">
            <a href="{{ url('list_users') }}">
            <i class="fa fa-long-arrow-left" aria-hidden="true"></i> Back
            </a>
        </div>
        <h2 class="main-heading">Edit User</h2>
        <div class="dash-all pt-0">
            <div class="dash-table-all" style="max-width:700px;">
                <form method="POST" action="{{ url('update_user_submit') }}">
                    <input type="hidden" name="id" value="{{$User->id}}">
                    @csrf

                    <table class="table table-striped">
                       
                        <tr>
                            <td>Employee ID<span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <input type="text" class="form-control @error('emp_id') is-invalid @enderror" name="emp_id" required autocomplete="off" value="{{$User->emp_id}}" readonly="">
                                @error('emp_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <td>Employee Name<span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <input type="text" class="form-control @error('emp_name') is-invalid @enderror" name="emp_name" required autocomplete="off" value="{{$User->emp_name}}" readonly="">
                                @error('emp_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>

                        
                        <tr>
                            <td>Email <span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <input type="text" class="form-control @error('email') is-invalid @enderror" name="email" required autocomplete="off" value="{{$User->email}}" readonly="">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>

                         <tr>
                            <td>Employee Phonenumber <span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <input type="text" class="form-control @error('emp_phonenumber') is-invalid @enderror" name="emp_phonenumber" required autocomplete="off" value="{{$User->emp_phonenumber}}" readonly="">
                                @error('emp_phonenumber')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>

                        <tr>
                            <td>Department <span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <input type="text" class="form-control @error('emp_department') is-invalid @enderror" name="emp_department" required autocomplete="off" value="{{$User->emp_department}}" readonly="">
                                @error('emp_department')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>


                        <tr>
                            <td>Employee Branch <span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <select class="form-control" name="emp_branch" readonly disabled>
                                    <option value="">Select</option>
                                    @foreach($branch as $val)
                                    <option value="{{$val->BranchID}}" {{ $User->emp_branch == $val->BranchID ? 'selected' : '' }}>{{$val->BranchName}}</option>
                                    @endforeach
                                </select>
                                
                            </td>
                        </tr>


                        
                        <tr>
                            <td>Employee Base location <span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <select class="form-control select21" name="emp_baselocation">
                                    <option value="">Select</option>
                                    @foreach($branch as $val)
                                        <option value="{{ $val->BranchID }}" {{ $User->emp_baselocation == $val->BranchID ? 'selected' : '' }}>
                                            {{ $val->BranchName }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <!--------------add------------------>
                        <tr>
                            <td>Exclude HRMS Base Location Update</td>
                            <td width="10">:</td>
                            <td>
                                <input type="checkbox" class="form-control-check" name="hrms_baselocation_flag" value="1" {{ old('hrms_baselocation_flag', $User->hrms_baselocation_flag ?? 0) ? 'checked' : '' }}>

                            </td>
                        </tr>
                        <!--------------add------------------>
                        <tr>
                            <td>Employee Designation <span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <input type="text" class="form-control @error('emp_designation') is-invalid @enderror" name="emp_designation" required autocomplete="off" value="{{$User->emp_designation}}" readonly="">
                                @error('emp_designation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <td>Employee Grade <span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <input type="text" class="form-control @error('emp_grade') is-invalid @enderror" name="emp_grade" required autocomplete="off" value="{{$User->emp_grade}}" readonly="">
                                @error('emp_grade')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <td>Reporting Person <span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                
                                <select class="form-control select22" name="reporting_person" id="reporting_person">
                                    <option value="">Select</option>
                                    @foreach($userData as $val)
                                    <option value="{{$val->emp_id}}" {{ $User->reporting_person_empid == $val->emp_id ? 'selected' : '' }}>{{$val->emp_name}} | {{$val->emp_id}}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" value="{{$User->reporting_person}}" id="reporting_person_name" name="reporting_person_name">
                            </td>
                        </tr>
                        <!--------------add------------------>
                        <tr>
                            <td>Exclude HRMS Reporting Person Update</td>
                            <td width="10">:</td>
                            <td>
                                <input type="checkbox" class="form-control-check" name="hrms_reporting_person_flag" value="1" {{ old('hrms_reporting_person_flag', $User->hrms_reporting_person_flag ?? 0) ? 'checked' : '' }}>
                            </td>
                        </tr>
                        <!--------------add------------------>
                        <tr>
                            <td>Employee Role <span style="color: red;">*</span></td>
                            <td width="10">:</td>
                            <td>
                                <input type="text" class="form-control @error('emp_role') is-invalid @enderror" name="emp_role" required autocomplete="off" value="{{$User->emp_role}}" readonly="">
                                @error('emp_role')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </td>
                        </tr>
<?php 
// dd($User);
?><input type="hidden" value="{{$User->id}}" id="" name="id">
                    </table>
                    <button class="button_orange">Update</button>
                </form>
            </div>
        </div>
    </div>
<script>
   $("#reporting_person").on('change', function() {
    var selectedText = $(this).find('option:selected').text(); // Get the selected option's text
    console.log(selectedText); // For debugging or further use
    $('#reporting_person_name').val(selectedText);
    });
</script>
<script>
    $(document).ready(function() {

        $('.select21').select2({
            placeholder: "Select a base location",
            allowClear: true
        });

        $('.select22').select2({
            placeholder: "Select a reporting person",
            allowClear: true
        });
    });
</script>
    @include("admin.include.footer")
</body>
</html>

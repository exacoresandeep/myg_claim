<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>MyG :: Claim Management Application</title>
<link rel="icon" href="favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

<!-- Bootstrap core CSS -->
<link href="{{ asset ('css/bootstrap.min.css') }}" rel="stylesheet">

<link rel="stylesheet" href="{{ asset ('font-awesome/css/font-awesome.min.css') }}">

<!-- Custom styles for this template -->
<link href="{{ asset ('css/style.css') }}" rel="stylesheet">
<style type="text/css">
  .parent_password{
  position: relative;
}
.eye_show {
  z-index: 9999;
  position: absolute;
  top: 30%;
  right: 10px;
}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body class="login-bg">

<div class="login-cover">
  <div class="logo-name">
    <img src="{{ asset ('images/logo-name.png') }}" class="login-logo">
  </div>  
  <div class="login-box">
    <h3>Employee Login</h3>
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true" style="color:red">&times;</span>
                </button>
            </div>
        @endif

        <div class="login-in">
        <form method="POST" action="{{ url('auth_login') }}">
        @csrf    
            <label>Username</label>
            <input type="text" class="form-control @error('username') is-invalid @enderror" placeholder="Enter your username" name="username" required autocomplete="off">
            @error('username')
                <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
                </span>
            @enderror
            <label>Password</label>
            <div class="parent_password">
            <input type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter your password" name="password" id="password" required autocomplete="off">
            <span toggle="#password_show" class="fa fa-fw fa-eye field_icon toggle-password eye_show"></span>
            </div>
            @error('password')
            <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
            </span>
          @enderror
            <button type="submit" class="btn btn-primary btn-login">Login</button> 
        </form>     
    </div>
  </div>
  <h6>
    2024-25 <i class="fa fa-copyright" aria-hidden="true"></i> myGourney Application.
  </h6>  
</div>


<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script type="text/javascript">
$(document).ready(function() {
    $(document).on('click', '.toggle-password', function() {
        $(this).toggleClass("fa-eye fa-eye-slash");
        var input = $("#password");
        input.attr('type') === 'password' ? input.attr('type','text') : input.attr('type','password')
    });
});
</script>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></scrip>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-slim.min.js"><\/script>')</script>
<script src="{{ asset ('js/bootstrap.min.js') }}"></script>
</body>
</html>

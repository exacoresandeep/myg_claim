<?php
use Illuminate\Support\Facades\Auth;

// Check if session keys exist
if (session()->has('Login_User_ID') && session()->has('Role')) {
    // If both session keys exist, display the Role
    // echo "Role: " . session('Role');
} else {
    // If session keys do not exist, log out the user and redirect to login
    Auth::logout();
    return redirect()->route('login')->with('message', 'Session expired. Please log in again.');
    exit; // Stop further script execution
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MyG Dashboard</title>
    <link rel="icon" href="favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="{{ asset ('css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset ('font-awesome/css/font-awesome.min.css') }}">
    <!-- <link href="{{ asset ('css/jquery.fancybox.css') }}" rel="stylesheet"> -->
    <link href="{{ asset ('css/style.css') }}" rel="stylesheet">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">>
    <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">  -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css">


    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js" integrity="sha512-BkpSL20WETFylMrcirBahHfSnY++H2O1W+UnEEO4yNIl+jI2+zowyoGJpbtk6bx97fBXf++WJHSSK2MV4ghPcg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style type="text/css">
        /* Custom styles for DataTables pagination */
        .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
        .dataTables_wrapper .dataTables_paginate .paginate_button.next {
            background-color: #E95815 !important; /* Orange background color */
            color: white !important; /* White text color */
            border-color: #E95815 !important; /* Border color */
            padding: 5px 10px; /* Adjust padding as needed */
            border-radius: 5px; /* Optional: Round corners */
        }

        /* Hover state */
        .dataTables_wrapper .dataTables_paginate .paginate_button.previous:hover,
        .dataTables_wrapper .dataTables_paginate .paginate_button.next:hover {
            background-color: #FFA500 !important; /* Lighter orange background on hover */
            border-color: #FFA500 !important; /* Lighter orange border color on hover */
        }

        .button_orange {
            background-color: #E95815;
            color: white;
            padding: 4px 5px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            transition-duration: 0.4s;
        }

        .button_orange:hover {
            background-color: #E95815;
            color: white;
            text-decoration:none;
        }
        .menu-container {
            position: absolute;
            height:500px;
            background-color: #111;
            border-right: 1px solid #ccc;
            overflow-y: auto;
            width: 100%;
        }
        .menu-container::-webkit-scrollbar {
            width: 8px; /* Width of the scrollbar */
            background-color: #ccc; /* Color of the scrollbar track */
            float:right;
        }

        /* Handle of the scrollbar */
        .menu-container::-webkit-scrollbar-thumb {
            background-color: #E95815; /* Color of the scrollbar handle */
            border-radius: 10px; /* Rounded corners of the scrollbar handle */
        }
        .ui-menu li{
           width: 100%;
            list-style-type: none; /* Hides the bullets */
            padding: 3px 1em 3px .4em;
            background-color:#FFF;
        
        }
        .ui-menu li:hover{
           
           list-style-type: none; /* Hides the bullets */
           padding: 3px 1em 3px .4em;
           background-color:#E95815;
           color:#FFF;
       
       }
        .ui-menu
        {
            position: relative;
            border: 1px solid #c5c5c5;
            width: 280px;
            z-index: 1000;
            background-color:#FFF;
            padding-left: 0px;
        }
        .dashbox-in a{
            text-decoration:none;
            color:#000;
        }
          
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light fixed-top bg-light">
        <a class="navbar-brand" href="{{ url('/home') }}">
            <img src="{{ asset ('images/myg-logo.png') }}">
        </a>
        <div class="date mr-auto">
            <?php date_default_timezone_set('Asia/Kolkata'); ?>
            <?php echo date('d-m-Y')?>,<?php echo date('l')?> | <?php echo date('h:i A')?>
        </div>
        <div class="account-sec ml-auto">
            <a href="{{ url('/notifications') }}" class="noti-sec">
                <i class="fa fa-bell" aria-hidden="true"></i>
                <span id="notification-count">0</span>
            </a>
            <span class="dropdown">      
                <a class="dropdown-toggle" data-toggle="dropdown">
                    <img src="{{asset('images/user-icon.png')}}"> <span>{{ Auth::user()->emp_name }}</span>
                </a>
                <div class="dropdown">
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="{{ url('view_user/'.Auth::user()->id) }}">User Profile</a>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </span>
        </div>
    </nav>

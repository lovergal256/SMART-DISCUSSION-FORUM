<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>

    <style>
        body{
            margin:0;
            font-family:Arial, Helvetica, sans-serif;
            background:#f4f6f9;
        }

        .header{
            background:#0d6efd;
            color:white;
            padding:20px;
        }

        .container{
            width:90%;
            margin:30px auto;
        }

        .card{
            background:white;
            padding:20px;
            border-radius:8px;
            box-shadow:0 0 10px rgba(0,0,0,.1);
        }

        h2{
            margin-top:0;
        }

        .menu{
            display:flex;
            gap:20px;
            margin-top:30px;
        }

        .box{
            flex:1;
            background:white;
            padding:25px;
            border-radius:8px;
            text-align:center;
            box-shadow:0 0 10px rgba(0,0,0,.1);
        }

        .box a{
            text-decoration:none;
            color:#0d6efd;
            font-weight:bold;
        }

        button{
            background:red;
            color:white;
            border:none;
            padding:10px 18px;
            cursor:pointer;
            border-radius:5px;
        }
    </style>

</head>

<body>

<div class="header">

    <h1>Student Dashboard</h1>

</div>

<div class="container">

    <div class="card">

        <h2>Welcome,
            {{ Auth::user()->FullName }}
        </h2>

        <p>Email :
            {{ Auth::user()->Email }}
        </p>

        <p>
            You have successfully logged into the Student Registration System.
        </p>

    </div>

    <div class="menu">

        <div class="box">
            <h3>My Profile</h3>

            <a href="#">
                View Profile
            </a>
        </div>

        <div class="box">
            <h3>Course Registration</h3>

            <a href="#">
                Register Courses
            </a>
        </div>

        <div class="box">
            <h3>Results</h3>

            <a href="#">
                View Results
            </a>
        </div>

    </div>

    <br><br>

    <form action="{{ route('logout') }}" method="POST">

        @csrf

        <button type="submit">
            Logout
        </button>

    </form>

</div>

</body>

</html>
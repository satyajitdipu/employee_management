<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie | Oauth Authorize</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }

        .login-container {
            background-color: #fff;
            padding: 0px 20px 20px 20px;
            border-radius: 34px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 387px;
            height: 375px;
            padding: 20px;
            position: relative;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        #allow {
            background-color: #007bff;
            font-size: small;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100px;
            position: absolute;
            bottom: 16px;
            float: right;
            right: 30px;
            height: 40px;
        }

        #cancel {
            background-color: #d5d9dd;
            font-size: small;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            height: 40px;
            width: 100px;
            position: absolute;
            bottom: 16px;
            float: right;
            right: 150px;
            color: black;
            text-decoration: none;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .user-container {
            padding: 4px 15px;
            background-color: #f8f8f8;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .user-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #333;
        }

        .user-email {
            font-size: 14px;
            color: #666;
        }

        .cookie-header {
            font-size: 14px;
            padding: 10px 0px 0px 0px;
            font-size: 400;
            color: #bbbbbb;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="cookie-header">Sign in with Cranberry Cookie</div>
        <hr />
        <h2>Login</h2>
        <form method="GET" action="{{ route('handleAuthorizationLogin') }}">
            <form action="">
                <div>
                    <input type="hidden" id="redirect_uri" name="redirect_uri" value="{{$redirect_uri}}">
                    <input type="hidden" id="response_type" name="response_type" value="{{$response_type}}">
                    <input type="hidden" id="client_id" name="client_id" value="{{$client_id}}">
                    <input type="hidden" id="scope" name="scope" value="{{$scope}}">
                    <input type="hidden" id="state" name="state" value="{{$state}}">
                </div>
                <div class="user-container">
                    @auth
                    <div>
                        <p class="user-name">{{ auth()->user()->name }}</p>
                        <p class="user-email">{{ auth()->user()->email }}</p>
                    </div>
                    <div>
                        <img src="/logout.png" alt="logout" height="32" width="32">
                    </div>
                    @else
                    <p>You are not logged in.</p>
                    @endauth
                </div>



                <div class="form-group">
                    <button type="submit" id="allow">Allow</button>
                    <a href="{{$redirect_uri}}" id="cancel">Deny</a>
                </div>
                @csrf
            </form>
    </div>
</body>

</html>
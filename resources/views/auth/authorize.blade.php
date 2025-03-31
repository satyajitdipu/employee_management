<!DOCTYPE html>
<html lang="en">

<head>
    <script>
        window.onbeforeunload = function() {
            // Display a custom confirmation alert
            //  confirm("Are you sure you want to leave? Any unsaved changes will be lost.");
            alert("dwd");
        };
    </script>
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
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
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

        button {
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
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
        <form method="POST" id="authorizationForm" action="{{ route('allow-auth') }}">
            <div>
                <input type="hidden" id="redirect_uri" name="redirect_uri" value="{{$redirect_uri}}">
                <input type="hidden" id="client_id" name="client_id" value="{{$client_id}}">
                <input type="hidden" id="state" name="state" value="{{$state}}">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Authorize</button>
            </div>
            @csrf
        </form>

    </div>
</body>

</html>
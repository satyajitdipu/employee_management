<!DOCTYPE html>
<html lang="en">

<head>
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized</title>
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

        .unauthorized-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

        h2 {
            color: #ff0000;
        }

        p {
            color: #333;
            margin-top: 10px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="unauthorized-container">
        <h2>Invalid user name or password</h2>
        <p>{{ $reason }}</p>
        <p><a href="#" onclick="goBack()">Go back to the previous page</a></p>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>

</html>
<?php
    session_start();

    if(isset($_POST['submit'])){
        $conn = mysqli_connect("localhost", "root", "", "googlelogin");
        if(!$conn) die("Connection failed: " . mysqli_connect_error());

        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        $emailCheckQuery = "SELECT * FROM users WHERE email='$email' LIMIT 1";
        $result = mysqli_query($conn, $emailCheckQuery);
        $user = mysqli_fetch_assoc($result);

        if($user){
            echoMessage("Error: This email is already registered.", "red");
        }else{
            $activationToken = bin2hex(random_bytes(16));

            $query = "INSERT INTO users (username, email, password) 
                    VALUES ('$username', '$email', '$password')";

            if(mysqli_query($conn, $query)){
                $activationLink = "http://localhost/CipherApp/activation.php?token=$activationToken";
                echoMessage("Successful registration! I will automatically activate your account.", "green");
            }else{
                echoMessage("Error: " . mysqli_error($conn), "red");
            }
        }

        mysqli_close($conn);
    }
    function echoMessage($message, $color){
        echo '<script>
        document.addEventListener("DOMContentLoaded", function(){
            var successMessage = document.createElement("div");
            successMessage.className = "toast";
            successMessage.style.backgroundColor = "' . $color . '";
            successMessage.textContent = "' . $message . '";
            document.body.appendChild(successMessage);

            setTimeout(function() {
                successMessage.remove();
            }, 3000);
        });
        </script>';
    }

?>


<!-- client secret = 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 -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-***********" crossorigin="anonymous">

    <style type="text/css">
        body{
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        form{
            width: 90.5%;
            background: white;
            padding: 2rem;
            border-radius: 4px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }

        .form-group{
            margin-bottom: 1rem;
        }

        h1{
            text-align: center;
        }

        label{
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        input[type="email"],
        input[type="password"]{
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input:hover,
        input:focus,
        input:hover,
        input:focus,
        input:hover,
        input:focus,
        input:hover,
        input:focus{
            border-color: #4dcf79;
            outline: none;
            box-shadow: 0 0 5px rgba(179, 230, 175, 0.5);
        }

        input[type="submit"]{
            padding: 0.5rem 1rem;
            background: #4dcf79;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .password-input{
            position: relative;
        }

        .toggle-password{
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .fa-eye-slash:before{
            content: "\f070";
        }

        .fa-eye:before{
            content: "\f06e";
        }

        .error{
            color: red;
        }

        #success-message{
            display: none;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
        }

        #success-message.show{
            display: block;
        }   
        
        a{
            color: green;
        }
        .toast{
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 15px;
            border-radius: 5px;
            color: white;
            z-index: 9999;
        }
   </style>
</head>
    
<body>

    <div class="container">  
        <div id="success-message" class="success">
        </div>

    <form action="" method="POST" onsubmit="return validateForm()">
        <h1>Create a user account</h1>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username">
            <span id="username-error" class="error"></span>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email">
            <span id="email-error" class="error"></span>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <div class="password-input">
                <input type="password" id="password" name="password">
                <span class="toggle-password" onclick="togglePasswordVisibility()">
                    <i class="fas fa-eye-slash"></i>
                </span>
            </div>
            <span id="password-error" class="error"></span>
        </div>

        <div class="form-group">
            <input type="submit" name="submit" value="Register">
        </div>

        <div id="login" class="login">
           <span>Already have an account? </span>
           <a href="login.php">Login</a>
        </div>
    </form>
</div>


    <script type="text/javascript">
        function togglePasswordVisibility(){
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password i');

            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            toggleIcon.classList.toggle('fa-eye-slash');
            toggleIcon.classList.toggle('fa-eye');
        }

        function validateForm(){
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const usernameError = document.getElementById('username-error');
            const emailError = document.getElementById('email-error');
            const passwordError = document.getElementById('password-error');

            usernameError.textContent = username.length < 6 ? 'Username must be at least 6 characters' : '';

            emailError.textContent = email.trim() === '' ? 'Email address is required' : '';

            passwordError.textContent = password.length < 8 || !(/[a-zA-Z0-9@#$%^&*]/.test(password)) 
            ? 'Password must be at least 8 characters and contain a combination of characters, numbers, and special characters.' : '';

            return usernameError.textContent === '' && passwordError.textContent === '' &&  emailError.textContent == '';
        }

    </script>
</body>
</html>

<?php
    session_start();
    require('./config.php');

    $loginUrl = $client->createAuthUrl();

    if(isset($_GET['code'])){
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if(isset($token['error'])){
            $errorMessage = "Google authentication error: " . $token['errorDescription'];
        } else{
            $_SESSION['token'] = $token;
            header('Location: cipher.php');
            exit;
        }
    }

    $errorMessage = "";

    if(isset($_POST['submit'])){
        $conn = mysqli_connect("localhost", "root", "", "googlelogin");
        if(!$conn){
            die("Connection failed: " . mysqli_connect_error());
        }

        $username = $_POST['username'];
        $password = $_POST['password'];

        if (isset($_SESSION['loginAttempts']) && $_SESSION['loginAttempts'] >= 3){
            $errorMessage = "The registered user is allowed 3 attempts (wrong password) before being blocked from the system. Please try again later.";
        }else{
            $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
            $result = mysqli_query($conn, $query);

            if(mysqli_num_rows($result) === 1){
                $_SESSION['loginAttempts'] = 0;
                $_SESSION['username'] = $username;
                header("Location: cipher.php");
                exit();
            }else{
                if (isset($_SESSION['loginAttempts'])){
                    $_SESSION['loginAttempts']++;
                    $attempts = $_SESSION['loginAttempts'];
                    switch ($attempts){
                        case 1:
                            $errorMessage = "Invalid username or password. Please try again. (1 / 3 attempts)";
                            break;
                        case 2:
                            $errorMessage = "Invalid username or password. Please try again. (2 / 3 attempts)";
                            break;
                        case 3:
                            $errorMessage = "Invalid username or password. Please try again. (3 / 3 attempts)";
                            break;
                        default:
                            $errorMessage = "Invalid username or password. Please try again.";
                            break;
                    }
                }else{
                    $_SESSION['loginAttempts'] = 1;
                    $errorMessage = "Invalid username or password. Please try again. (1 attempt)";
                }
            }
        }

        mysqli_close($conn);
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-***********" crossorigin="anonymous">
</head>
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
        
        .login a{
            color: green;
        }

        .btn{
            display: flex;
            justify-content: center;
            padding: 30px;
        }

        .btn a{
            all: unset;
            cursor: pointer;
            padding: 5px;
            display: flex;
            width: 150px;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            background-color: #f9f9f9;
            border: 1px solid rgba(0, 0, 0, .2);
            border-radius: 3px;
        }

        .btn a:hover{
            background-color: #ffffff;
            border: 1px solid #4dcf79;
        }

        .btn img{
            width: 20px;
            margin-right: 5px;

        }
   </style>
<body>

<div class="container">
    <?php
        if(!empty($errorMessage)){
            echo '<div style="text-align: center; margin-top: 20px; color: red;">';
            echo $errorMessage;
            echo '</div>';
        }
    ?>
    <div id="success-message" class="success">
        Login successful!
    </div>

    <form action="" method="POST" onsubmit="return validateForm()">
        <h1>Login to your account</h1>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username">
            <span id="username-error" class="error"></span>
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
            <input type="submit" name="submit" id="submit" value="Signin">
        </div>

        <div class="btn">
            <a href="<?= $loginUrl ?>"><img src="https://tinyurl.com/46bvrw4s" alt="Google Logo"> Sign in with Google</a>
        </div>

        <div id="login" class="login">
            <span>Don't have an account? </span>
            <a href="register.php">Register</a>
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

</script>
</body>
</html>

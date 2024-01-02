<?php
    session_start();
    include 'conn.php';
    require('./config.php');

    if(!isset($_SESSION['token'])){
        header("Location: login.php");
        exit();
    }

    $client->setAccessToken($_SESSION['token']);

    if($client->isAccessTokenExpired()){
        header('Location: logout.php');
        exit;
    }

    $googleOauth = new Google_Service_Oauth2($client);
    $userInfo = $googleOauth->userinfo->get();

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $id = $userInfo['id'];
        $email = $userInfo['email'];

        $selectUser = $conn->prepare("SELECT * FROM `users` WHERE `email` = :email");
        $selectUser->bindParam(':email', $email);
        $selectUser->execute();
        $userData = $selectUser->fetch(PDO::FETCH_ASSOC);

        if(!$userData){
            header("Location: login.php");
            exit;
        }

        $username = $userData['username'];

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }


    // Atbash cipher function
    function atbashCipher($text){
        $result = '';
        $uppercaseA = ord('A');
        $uppercaseZ = ord('Z');
        $uppercaseOffset = $uppercaseZ - $uppercaseA;

        foreach(str_split($text) as $char){
            if(ctype_upper($char)){
                $result .= chr($uppercaseA + $uppercaseOffset - (ord($char) - $uppercaseA));
            } elseif (ctype_lower($char)){
                $result .= chr(ord('a') + ($uppercaseOffset - (ord($char) - ord('a'))));
            } else{
                $result .= $char;
            }
        }

        return $result;
    }

    // Caesar cipher function
    function caesarCipher($text, $shift){
        $result = '';
        $uppercaseA = ord('A');
        $uppercaseZ = ord('Z');
        $lowercaseA = ord('a');
        $lowercaseZ = ord('z');
        
        foreach(str_split($text) as $char){
            if (ctype_upper($char)){
                $result .= chr(($uppercaseA + (ord($char) - $uppercaseA + $shift)) % 26 + $uppercaseA);
            } elseif(ctype_lower($char)){
                $result .= chr(($lowercaseA + (ord($char) - $lowercaseA + $shift)) % 26 + $lowercaseA);
            } else{
                $result .= $char;
            }
        }
        
        return $result;
    }

    // Vigenere cipher function
    function vigenereCipher($text, $key){
        $result = '';
        $keyLength = strlen($key);
        $uppercaseA = ord('A');
        
        for($i = 0; $i < strlen($text); $i++){
            $char = $text[$i];
            if(ctype_upper($char)){
                $shift = ord($key[$i % $keyLength]) - $uppercaseA;
                $result .= chr(($uppercaseA + (ord($char) - $uppercaseA + $shift)) % 26 + $uppercaseA);
            } elseif (ctype_lower($char)){
                $shift = ord($key[$i % $keyLength]) - $uppercaseA;
                $result .= chr(($uppercaseA + (ord($char) - $uppercaseA + $shift)) % 26 + $uppercaseA);
            } else{
                $result .= $char;
            }
        }
        
        return $result;
    }


    $encryptedUsername = '';

    if(isset($_POST['submit'])){
        $cipherOption = isset($_POST['cypherOptions']) ? $_POST['cypherOptions'] : 'chooseciphers';

        if($cipherOption === 'atbash'){
            $encryptedUsername = atbashCipher($username);
        }elseif($cipherOption === 'caesar'){
            $shift = isset($_POST['caesarShift']) ? (int)$_POST['caesarShift'] : 0;
            $encryptedUsername = caesarCipher($username, $shift);
        }elseif($cipherOption === 'vigenere'){
            $key = isset($_POST['vigenereKey']) ? $_POST['vigenereKey'] : '';
            $encryptedUsername = vigenereCipher($username, $key);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cipher</title>
    <style>
        .container{
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .container h1{
            font-size: 24px;
            margin-bottom: 20px;
        }

        .container select, .container input[type="text"]{
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        .container #output{
            border: 1px solid #ccc;
            padding: 10px;
            min-height: 100px;
            background-color: #fff;
            border-radius: 3px;
        }

        .container input[type="submit"]{
            background-color: #4dcf79;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 3px;
            font-weight: bold;
        }

        .container input[type="submit"]:hover{
            background-color: #4dcf90;
        }


        input[name="caesarShift"],
        input[name="vigenereKey"]{
            width: 380px !important; 
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        input[name="caesarShift"]:focus,
        input[name="vigenereKey"]:focus{
            border-color: #4dcf79;
            outline: none;
            box-shadow: 0 0 5px #4dcf79;
        }

        input[name="caesarShift"]::placeholder,
        input[name="vigenereKey"]::placeholder{
            color: #999;
        }

        input[name="submit"]{
            margin-top: 50px
        }

        .container a.logout-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ff6347;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
            margin-top: 20px; 
            transition: background-color 0.3s;
        }

        .container a.logout-button:hover {
            background-color: #ff4233; 
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Cipher Option Selector</h1>
        <form action="cipher.php" method="post">
            <select id="cypherOptions" name="cypherOptions" onchange="toggleInputFields()">
                <option value="chooseciphers">ALL CIPHERS</option>
                <option value="atbash">ATBASH</option>
                <option value="caesar">CAESAR</option>
                <option value="vigenere">VIGENERE</option>
            </select>

            <input type="text" name="caesarShift" placeholder="Enter Caesar Shift (0-25)">
            <input type="text" name="vigenereKey" placeholder="Enter Vigenere Key">

            <div id="output" class="output">
                <strong>Username: </strong><?php echo $username; ?><br>
                <?php
                $cipherOption = isset($_POST['cypherOptions']) ? $_POST['cypherOptions'] : 'chooseciphers';

                if($cipherOption === 'chooseciphers'):?>
                    <strong>Choose a cipher</strong>
                <?php elseif($cipherOption === 'atbash'): ?>
                    <strong>CipherText(Atbash): </strong><?php echo $encryptedUsername;?>
                <?php elseif($cipherOption === 'caesar'): ?>
                    <strong>CipherText(Caesar): </strong><?php echo $encryptedUsername;?>
                <?php elseif($cipherOption === 'vigenere'): ?>
                    <strong>CipherText(Vigenere): </strong><?php echo $encryptedUsername;?>
                <?php endif; ?>
            </div>
            <input type="submit" name="submit" value="Encrypt">
        </form>
          <a href="./logout.php" class="logout-button">Logout</a>
    </div>
</body>

<script>
    function toggleInputFields(){
        var selectElement = document.getElementById("cypherOptions");
        var caesarInput = document.getElementsByName("caesarShift")[0];
        var vigenereInput = document.getElementsByName("vigenereKey")[0];

        if (selectElement.value === "caesar"){
            caesarInput.style.display = "block";
            vigenereInput.style.display = "none";
        } else if (selectElement.value === "vigenere"){
            caesarInput.style.display = "none";
            vigenereInput.style.display = "block";
        } else{
            caesarInput.style.display = "none";
            vigenereInput.style.display = "none";
        }
    }

    toggleInputFields();
</script>

</html>

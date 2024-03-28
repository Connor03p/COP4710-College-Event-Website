<?php 
    require_once $_SERVER["DOCUMENT_ROOT"] . '\php\database.php';
    global $conn;
?>

<?php // Form Handling
    if (isset($_POST['login'])) 
    {
        $data_events = checkLogin();
        if($data_events === true)
        {
            header('location: dashboard');
        }
        else
        {
            $loginError = $data_events;
            $loginUsername = $_POST['username'];
            $loginPassword = $_POST['password'];
        }
    }

    function checkLogin()
    {
        global $conn;
        $input_name = $_POST['username'];
        $input_pass = $_POST['password'];

        $query = "SELECT * from users WHERE username = '$input_name'";
        $query = $conn->prepare($query);
        $query->execute();
        $user = $query->get_result()->fetch_assoc();

        if (!$user) 
        {
            return 'User not found';
        }

        if (!password_verify($input_pass, $user['password']))
        {
            return 'Password incorrect';
        }

        $_SESSION['user'] = $user;

        return $user['username'] == $input_name;
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <meta name="description" content="">

        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <main>
            <header>
                <h1>University Events</h1>
            </header>
            <div class="break-line"></div>

            <?php if(isset($loginError)) { ?>
                <div class="error">
                    <p class="center"><?php echo $loginError; ?></p>
                </div>
            <?php } ?>
            
            <form id="form-login" method="POST">
                <div>
                    <label for="input-username">Username</label>
                    <div>
                        <input type="text" name="username" id="input-username" placeholder="Username" required
                            <?php 
                                if (isset($loginUsername)) { echo "value='" . $loginUsername . "'"; } 
                                if (isset($loginError) && $loginError == 'User not found') echo 'title="User not found"';
                            ?>
                        >
                        <span></span>
                    </div>
                </div>
                
                <div>
                    <label for="input-password">Password</label>
                    <div>
                        <input type="password" name="password" id="input-password" placeholder="Password" required
                            <?php 
                                if (isset($loginPassword)) { echo "value='" . $loginPassword . "'"; }
                                if (isset($loginError) && $loginError == 'Password incorrect') echo 'title="Password incorrect"';
                            ?> 
                        >
                        <span></span>
                    </div>
                </div>               
                
                <input type="submit" name="login" value="Login">
            </form>

            <p style="text-align: center;">Don't have an account? <a href="signup">Click here to create one!</a></p>
 
        </main>
    </body>
</html>
<?php
    global $conn;

    if (isset($_POST['signup']))
    {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];            
        
        try
        {
            $query = $conn->prepare("INSERT INTO `users` (role, username, email, password) VALUES (?, ?, ?, ?)");
            $query->bind_param("ssss", $role, $username, $email, $password);
            $query->execute();
            $query = $conn->close();

            header("Location: /");
        }
        catch(Exception $e)
        {
            $error = $e->getMessage();
            $restoreInput = $_POST;
        }
    }
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sign Up</title>
        <meta name="description" content="">

        <link rel="stylesheet" href="../style.css">
    </head>

    <body>
        <main>
            <header>
                <h1>Sign Up</h1>
            </header>
            <div class="break-line"></div>

            <?php if (isset($error)):; ?>
                <div class="error">
                    <p class="center">ERROR: <?php echo $error; ?></p>
                </div>
            <?php endif; ?>

            <noscript>
                <section>
                    <p>NOTE: If you are signing up as a super admin, you are not required to specify a university.</p>
                </section>
            </noscript>
            

            <form id="form-signup" method="POST">
                <div id="signup-role">
                    <label for="input-role">Role:</label>
                    <select id="input-role" name="role" required>
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                        <option value="super">Super Admin</option>
                    </select>
                </div>

                <div id="signup-username">
                    <label for="input-username">Username:</label>
                    <div>
                        <input type="text" id="input-username" placeholder="Username" name="username" required 
                            <?php if (isset($restoreInput)) echo "value='" . $restoreInput['username'] . "'" ?>
                        />
                        <span></span>
                    </div>
                </div>
                
                <div id="signup-email">
                    <label for="input-email">Email:</label>
                    <div>
                        <input type="email" id="input-email" placeholder="Email" name="email" required 
                            <?php if (isset($restoreInput)) echo "value='" . $restoreInput['email'] . "'" ?>
                        />
                        <span></span>
                    </div>
                </div>
                
                <div id="signup-password">
                    <label for="input-password">Password:</label>
                    <div>
                        <input type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" id="input-password" placeholder="Password" name="password" required 
                            <?php if (isset($restoreInput)) echo "value='" . $restoreInput['password'] . "'" ?>
                        />
                        <span></span>
                    </div>
                </div>

                <input type="submit" name="signup" value="Sign Up">
            </form>

            <p style="text-align: center;">Already have an account? <a href="/">Login here!</a></p>
        </main>

        <script>  
            const role_input = document.getElementById('input-role');
            const university = document.getElementById('signup-university');

            role_input.addEventListener('change', function() {
                if (role_input.value === 'super')
                {
                    university.hidden = true;
                }
                else
                {
                    university.hidden = false;
                }
            });

        </script>
    </body>
</html>
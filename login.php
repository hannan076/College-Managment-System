<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
   <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body id="body">
    <center><h2 class="mainhead">SIGN IN</h2></center>
<section class="container main_login">
    <div class="row justify-content-center">
        <div class="col-4">
            <form method="post">
            <input type="text" placeholder="Username or Email" class="form-control mb-3" name="username">
            <input type="password" placeholder="Password" class="form-control mb-3" name="password">

            <button class="w-50" id="btn" name="btn_login">Login</button>
            </form>
            <p class="signup">Don't have an account <a href="Register.php">sign up</a></p>
        
        </div>
    </div>
</section>
</body>
</html>

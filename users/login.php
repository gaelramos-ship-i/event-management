<?php
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $pass = htmlspecialchars(trim($_POST['pass']));
    
    $isFormOk = true;
    
    if (empty($email) || empty($pass)) {
        $message = "Tous les champs doivent être renseignés";
        $isFormOk = false;
    }
    
    if ($isFormOk && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Le mail n'est pas valide";
        $isFormOk = false;
    }
    
    if ($isFormOk) {
        require_once "../config/connect.php";
        
        $sql = "SELECT COUNT(email_user) AS nbEmail FROM users WHERE email_user = :email";
        $data = $db->prepare($sql);
        $data->execute([
            'email' => $email
        ]);

        $nbEmail = $data->fetch();

        if ($nbEmail[0] < 1) {
            $message = "Connexion impossible";
            $isFormOk = false;
        }

        if ($isFormOk) {
            $sql = "SELECT pass_user AS pass, firstname_user, lastname_user, id_user FROM users WHERE email_user = :email";
            
            $data = $db->prepare($sql);
            $data->execute([
                'email' => $email
            ]);

            $data = $data->fetch();

            if (password_verify($pass, $data['pass'])) {
                
                session_start();
                $_SESSION['firstname'] = $data['firstname_user'];
                $_SESSION['lastname'] = $data['lastname_user'];
                $_SESSION['id_user'] = $data['id_user'];
                $_SESSION['email'] = $email;

                header('Location: profile.php');
                exit;
            }
            $message = "Mail ou mot de passe invalide";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
</head>

<body>

    <h1>Connexion</h1>
    <a href="register.php">Pas encore inscrit ?</a>
    <?= $message; ?>
    <form action="#" method="post">
        <label for="email">Email :</label>
        <input type="text" id="email" name="email">
        <br>
        <label for="pass">Mot de passe :</label>
        <input type="password" id="pass" name="pass">
        <br>
        <button>Se Connecter !</button>
    </form>

</body>

</html>
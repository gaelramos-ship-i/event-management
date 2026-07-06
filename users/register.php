<?php
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $pass = htmlspecialchars(trim($_POST['pass']));
    
    $isFormOk = true;
    
    if (empty($firstname) || empty($lastname) || empty($email) || empty($pass)) {
        $message = "Tous les champs doivent être renseignés";
        $isFormOk = False;
    }

    if ($isFormOk && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Le mail n'est pas valide";
        $isFormOk = false;
    }

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $firstname = ucfirst($firstname);
    $lastname = ucfirst($lastname);

    if ($isFormOk) {

        require_once "../config/connect.php";

        $sql = "SELECT COUNT(email_user) AS nbEmail FROM users WHERE email_user = :email";

        $data = $db->prepare($sql);

        $data->execute([
            'email' => $email
        ]);

        $nbEmail = $data->fetch();

        if($nbEmail[0] == 1){
            $message = "Vous ne pouvez pas vous inscrire avec cette adresse mail";
            $isFormOk = false;
        }

        if($isFormOk){

            $sql = "INSERT INTO users(firstname_user, lastname_user, email_user, pass_user, fk_id_role) VALUES(:firstname, :lastname, :email, :pass, :id_role)";

            $req = $db->prepare($sql);

            $succes = $req->execute([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'pass' => $hash,
                'id_role' => 3
            ]);

            if($succes && $req->rowCount() > 0){
                header('Location: login.php');
                exit;
            }

            if(!$succes){
                $message = "Quelque chose s'est mal passé.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
</head>

<body>

    <h1>Inscription</h1>
    <a href="login.php">Se connecter !</a>
    <?= $message; ?>
    <form action="#" method="post">
        <label for="fistname">Prénom :</label>
        <input type="text" id="firstname" name="firstname">
        <br>
        <label for="lastname">Nom :</label>
        <input type="text" id="lastname" name="lastname">
        <br>
        <label for="email">Email :</label>
        <input type="text" id="email" name="email">
        <br>
        <label for="pass">Mot de passe :</label>
        <input type="password" id="pass" name="pass">
        <br>
        <button>S'inscrire !</button>
    </form>

</body>

</html>
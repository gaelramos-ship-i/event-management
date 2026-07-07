<?php session_start();
require_once '../config/connect.php';

if (!isset($_SESSION["firstname"])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) && $_GET['action'] == 'cancel') {

    $req = "DELETE FROM events_has_users WHERE fk_id_user = :id_user AND fk_id_event = :id_event";

    $datas = $db->prepare($req);

    $datas->execute([
        'id_user' => $_SESSION['id_user'],
        'id_event' => $_GET['id']
    ]);
}

if (isset($_GET['id']) && $_GET['action'] == 'subscribe') {

    $req = "INSERT INTO events_has_users (fk_id_user, fk_id_event) VALUE (:id_user, :id_event)";

    $datas = $db->prepare($req);

    $datas->execute([
        'id_user' => $_SESSION['id_user'],
        'id_event' => $_GET['id']
    ]);
}

if (isset($_GET['id']) && $_GET['action'] == 'delete') {

    $req = "DELETE FROM events WHERE id_event = :id_event";

    $datas = $db->prepare($req);

    $datas->execute([
        'id_event' => $_GET['id']
    ]);
}

//  Role organisateur 

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $date = htmlspecialchars($_POST['date']);
    $place = htmlspecialchars($_POST['place']);
    $desc = htmlspecialchars($_POST['desc']);

    $isFormOk = true;

    if (empty($title) || empty($date) || empty($place) || empty($desc)) {
        $message = "Tous les champs doivent être renseignés";
        $isFormOk = false;
    }

    if ($isFormOk) {

        $data = $db->prepare("INSERT INTO events (title_event, date_event, place_event, description_event, fk_id_user) VALUE (:title, :date, :place, :desc, :id_user)");

        $results = $data->execute([
            'title' => $title,
            'date' => $date,
            'place' => $place,
            'desc' => $desc,
            'id_user' => $_SESSION['id_user']
        ]);

        header('location: ./profile.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil</title>
</head>

<body>
    <h1>Bonjour <?= $_SESSION['firstname'] ?> !</h1>
    <a href="logout.php">Se déconnecter !</a>
    <h2>Les événements :</h2>
    <?php
    $data = $db->prepare("SELECT id_event, title_event AS title, description_event AS description, date_event AS date, place_event AS place FROM events");

    $data->execute();

    $results = $data->fetchAll();

    foreach ($results as $result) {
    ?>
        <article>
            <h2><?= $result['title'] ?></h2>
            <p><?= $result['description'] ?></p>
            <p><?= $result['place'] ?> le <?= date("d/m/Y", strtotime($result['date'])) ?> </p>
            <a href="?id=<?= $result['id_event'] ?>&action=subscribe">S'inscrire !</a>
        </article>
    <?php
    } ?>

    <h2>Mes inscriptions !</h2>
    <?php
    $data = $db->prepare("SELECT e.id_event, e.title_event, e.description_event, e.date_event, e.place_event FROM events AS e INNER JOIN events_has_users AS eu ON e.id_event = eu.fk_id_event WHERE eu.fk_id_user = :id");

    $data->execute(
        [
            'id' => $_SESSION['id_user']
        ]
    );

    $results = $data->fetchAll();

    if (!$results) {
        echo "<p>Vous n'êtes inscrit à aucun événements !</p>";
    }

    foreach ($results as $result) {
    ?>
        <article>
            <h2><?= $result['title_event'] ?></h2>
            <p><?= $result['description_event'] ?></p>
            <p><?= $result['place_event'] ?> le <?= date("d/m/Y", strtotime($result['date_event'])) ?> </p>
            <a href="?id=<?= $result['id_event'] ?>&action=cancel">Annuler !</a>
        </article>
    <?php
    }

    $data = $db->prepare("SELECT fk_id_role FROM users WHERE id_user = :id");

    $data->execute([
        'id' => $_SESSION['id_user']
    ]);

    $result = $data->fetch();

    $id_role = $result['fk_id_role'];

    if ($id_role == 2) { ?>

        <h2>Mon espace organisateur :</h2>

        <?= $message; ?>

        <form action="#" method="post">
            <label for="title">Titre :</label>
            <input type="text" id="title" name="title">
            <br>
            <label for="date">Date :</label>
            <input type="date" id="date" name="date">
            <br>
            <label for="place">Lieu :</label>
            <input type="text" id="place" name="place">
            <br>
            <label for="desc">Description :</label>
            <textarea name="desc" id="desc" placeholder="Décrire votre événement"></textarea>
            <br>
            <button>Ajouter</button>
        </form>
    <?php
    }

    $req = "SELECT id_event, title_event, description_event, date_event, place_event FROM events WHERE fk_id_user = :id_user";

    $data = $db->prepare($req);

    $data->execute([
        'id_user' => $_SESSION['id_user']
    ]);

    $results = $data->fetchAll();

    echo '<h2>Mes événements : </h2>';

    if (!$results) {
        echo "Vous n'organisez aucun événements";
    }

    foreach ($results as $result) {
        $req = "SELECT u.firstname_user, u.lastname_user, u.email_user 
        FROM users AS u INNER JOIN events_has_users AS eu ON eu.fk_id_user = u.id_user 
        WHERE eu.fk_id_event = :id_event";

        $data = $db->prepare($req);

        $data->execute([
            'id_event' => $result['id_event']
        ]);

        $users = $data->fetchAll();
    ?>
        <article>
            <h2><?= $result['title_event'] ?></h2>
            <p><?= $result['description_event'] ?></p>
            <p><?= $result['place_event'] ?> le <?= date("d/m/Y", strtotime($result['date_event'])) ?> </p>
            <a href="./modify.php?id=<?= $result['id_event'] ?>">Modifier !</a>
            <a href="?id=<?= $result['id_event'] ?>&action=delete">Annuler !</a>
        </article>

        <?php
        echo "<h3>Liste des participants</h3>";
        foreach ($users as $user) {
        ?>
            <div>
                <ul>
                    <li><?= $user['firstname_user'] ?> | <?= $user['lastname_user'] ?> | <?= $user['email_user'] ?></li>
                </ul>
            </div>
        <?php
        }
    }

    if ($id_role == 1) {
        ?>
        <h2>Mon espace administrateur :</h2>
        <?php


        $req = "SELECT firstname_user, lastname_user, email_user FROM users";

        $data = $db->prepare($req);

        $data->execute();

        $results = $data->fetchAll();

        foreach ($results as $result) {
        ?>
            <ul>
                <li><?= $result['firstname_user'] ?> | <?= $result['lastname_user'] ?> | <?= $result['email_user'] ?></li>
            </ul>
    <?php
        }
    }
    ?>
</body>

</html>
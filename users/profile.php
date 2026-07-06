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
        exit;
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
    } ?>

</body>

</html>
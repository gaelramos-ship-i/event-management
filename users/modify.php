<?php session_start();
require_once '../config/connect.php';
$message = "";
$idEvent = $_GET['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $date = htmlspecialchars($_POST['date']);
    $place = htmlspecialchars($_POST['place']);
    $desc = htmlspecialchars($_POST['desc']);

    // if (empty($title) || empty($date) || empty($place) || empty($desc)) {
    //     $message = "Tous les champs doivent être renseignés";
    //     $isFormOk = false;
    // }

    $sql = "UPDATE events SET title_event = :title, date_event = :date, place_event = :place, description_event = :desc WHERE id_event = :id_event";

    $data = $db->prepare($sql);

    $data->execute([
        'title' => $title,
        'date' => $date,
        'place' => $place,
        'desc' => $desc,
        'id_event' => $idEvent
    ]);
    header("Location: profile.php");
}

$req = "SELECT title_event, description_event, date_event, place_event FROM events WHERE id_event = :id_event";

$datas = $db->prepare($req);

$datas->execute([
    'id_event' => $idEvent
]);

$results = $datas->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <h1>Modifier</h1>

    <form action="#" method="post">
        <label for="title">Titre :</label>
        <input type="text" id="title" name="title" value="<?= $results['title_event'] ?>">
        <br>
        <label for="date">Date :</label>
        <input type="date" id="date" name="date" value="<?= $results['date_event'] ?>">
        <br>
        <label for="place">Lieu :</label>
        <input type="text" id="place" name="place" value="<?= $results['place_event'] ?>">
        <br>
        <label for="desc">Description :</label>
        <textarea name="desc" id="desc" placeholder="Décrire votre événement"><?= $results['description_event'] ?></textarea>
        <br>
        <button>Modifier</button>
    </form>

</body>

</html>
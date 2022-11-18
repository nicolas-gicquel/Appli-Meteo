<?php
$ville=NULL;
setlocale(LC_TIME, "fr_FR");

if ($_GET){
$ville = $_GET['ville'];

//Requête de l'API Openweather//
$ApiUrl = "https://api.openweathermap.org/data/2.5/weather?q=$ville&units=metric&lang=fr&appid=7a28ac9b054e2ff1460f3bf30e33db92&lang=fr";

//Connexion au serveur avec Curl//
$ch = curl_init();

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $ApiUrl);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);

curl_close($ch);
//Fin de connexion//

//Décodage des données afin de pour les exploiter//
$data = json_decode($response);
// var_dump($data);


//Initialisation de la varible temps//
$currentTime = time();
}
?>

<?php
require('app.php');
$date = new DateTime();
$date_fr = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
$date_fr->setPattern('EEEE d MMMM y');
$hour_fr = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
date_default_timezone_set('Europe/Paris');
$hour_fr=date('H:i');


?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon appli météo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin="" />
</head>

<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Mon Appli Météo</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                </ul>
                <form class="d-flex">
                    <input class="form-control me-2" placeholder="Ville" name="ville">
                    <button class="btn btn-outline-success" type="submit">Chercher</button>
                </form>
            </div>
        </div>
    </nav>
    <!-- Contenu de l'application -->
    <div class="report-container ">

        <?php

        if (isset($ville)) {
            if ($data->cod != 404) {

        ?>
                <div class="card">
                    <h5 class="card-header"><?= $data->name; ?></h5>
                    <div class="card-body">
                        <h5 class="card-title"><?= ucwords($date_fr->format($date)) ?> à <?= $hour_fr ?> (heure française)</h5>
                        <div class="card-content">
                            <div class="left">
                                <img src="http://openweathermap.org/img/w/<?= $data->weather[0]->icon; ?>.png" class="weather-icon" />
                            </div>
                            <div class="center">
                                <p class="card-text">T°max: <?= $data->main->temp_max; ?>°C</p>
                                <p class="card-text">T°min: <?= $data->main->temp_min; ?>°C</p>
                            </div>
                            <div class="right">
                                <p class="card-text">Humidité: <?= $data->main->humidity; ?> %</p>
                                <p class="card-text">Vent: <?= $data->wind->speed; ?> km/h</p>
                            </div>
                        </div>
                        <div class="wikipics"></div>
                        <div id="map"></div>
                    </div>
                </div>
            <?php  } else { ?>
                <div class="card">
                    <h5 class="card-header">Cette ville n'xiste pas dans notre base de données</h5>
                </div>
    </div>

<?php }
        } ?>
</div>

<!-- La video de fond -->
<video autoplay muted loop id="myVideo">
    <source src="clouds.mp4" type="video/mp4">
</video>



<!-- Script de connexion à l'API de wikipedia -->
<script>
    $(document).ready(function() {
        var articles = $('.wikipics');
        var toSearch = '';
        var searchUrl = 'https://en.wikipedia.org/w/api.php';

        var ajaxArticleData = function() {
            $.ajax({
                url: searchUrl,
                dataType: 'jsonp',
                data: {
                    action: 'query',
                    format: 'json',
                    generator: 'search',
                    gsrsearch: toSearch,
                    gsrnamespace: 0,
                    gsrlimit: 1,
                    prop: 'pageimages',
                    piprop: 'thumbnail',
                    pilimit: 'max',
                    pithumbsize: 400
                },
                success: function(json) {
                    var pages = json.query.pages;
                    $.map(pages, function(page) {
                        var pageElement = $('<div>');
                        if (page.thumbnail) pageElement.append($('<img class="photoVille">').attr('height', 250).attr('src', page.thumbnail.source));
                        articles.append(pageElement);
                    });
                }
            });
        };
        articles.empty();
        toSearch = "<?= $data->name ?>";
        ajaxArticleData();


    });
</script>

<!-- Script de connexion à l'API d'Openstreetmap -->
<script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js" integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script>
<script type="text/javascript">
    // On initialise la latitude et la longitude de Paris (centre de la carte)
    var lat = "<?= $data->coord->lat; ?>";
    var lon = "<?= $data->coord->lon; ?>";
    var macarte = null;
    // Fonction d'initialisation de la carte
    function initMap() {
        // Créer l'objet "macarte" et l'insèrer dans l'élément HTML qui a l'ID "map"
        macarte = L.map('map').setView([lat, lon], 12);
        // Leaflet ne récupère pas les cartes (tiles) sur un serveur par défaut. Nous devons lui préciser où nous souhaitons les récupérer. Ici, openstreetmap.fr
        L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
            // Il est toujours bien de laisser le lien vers la source des données
            attribution: 'données © <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
            minZoom: 5,
            maxZoom: 15
        }).addTo(macarte);
        var marker = L.marker([lat, lon]).addTo(macarte);
        marker.bindPopup('<?= $data->name; ?>');
    }
    window.onload = function() {
        // Fonction d'initialisation qui s'exécute lorsque le DOM est chargé
        initMap();
    };
</script>

</body>

</html>
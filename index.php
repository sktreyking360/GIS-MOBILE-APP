<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DRMD Disaster Incident</title>
    <!-- Include Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <!-- Include Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <!-- Include SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.css" />
    <!-- Include SweetAlert JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- PapaParse for CSV parsing -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.0/papaparse.min.js"></script>
    
    <style>
        #map {
            height:100%;
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: 'Roboto', sans-serif;
        }

        .logo {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            height: 30px;
            width: auto;
            padding: 0px;
            margin-top: 0.5%;
        }

        .left-logo {
            left: 10px;
        }

        .right-logo {
            right: 10px;
        }

        .content {
            display: flex;
            height: calc(100% - 40px);
            top: 40px;
            position: absolute;
        }

        .left-column {
            width: 30%;
            height: 100%;
            background-color: #f2f2f2;
        }

        .right-column {
            width: 70%;
            height: 100%;
            position: relative;
        }
        
        .header-logo {
            height: 60px;
            margin-top: 0.5%;
        }

        .table-container {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
        }

        .table th, .table td {
            padding: 8px;
        }

        .btn-view {
            background-color: green;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .btn-view:hover {
            background-color: #0056b3;
        }

        @media only screen and (max-width: 1920px) {
            .header {
                height: 60px;
                line-height: 60px;
                font-size: 30px;
            }

            .logo {
                height: 60px;
            }

            .header-logo {
                height: 60px;
                width: auto;
            }

            .content {
                padding-top: 50px;
            }
        }

        .header-text {
            display: inline;
        }

        .mobile-header-text {
            display: none;
        }

        @media only screen and (max-width: 768px) {
            .header {
                height: 80px;
                line-height: 80px;
                font-size: 40px;
            }

            .logo {
                height: 50%;
            }

            .header-logo {
                height: 50%;
                width: auto;
            }

            .search-container {
                 position: relative;
                top: 20%;
                width: 75%;
               
            }

            .search-container input[type=text] {
                width: 80%;
            }

            .search-container button {
                width: 18%;
            }
        }

        .search-container {
            position: absolute;
            top: 15%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            display: flex;
            align-items: center;
        }

        .search-container input[type=text] {
            padding: 10px;
            font-size: 14px;
            border: none;
            width: 200px;
            margin-right: 0px;
        }

        .search-container button {
            padding: 10px 15px;
            background: green;
            color: white;
            font-size: 14px;
            border: none;
            cursor: pointer;
            width: 100px;
        }

        .search-container button:hover {
            background: #2e3192;
            transition: 0.3s;
        }

        .dashboard-button {
            position: absolute;
            top: 22%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            display: flex;
            align-items: center;
        }

        .dashboard-button button {
            padding: 10px 15px;
            background: #2e3192;
            color: white;
            font-size: 14px;
            border: none;
            cursor: pointer;
            width: 150px;
        }

        .dashboard-button button:hover {
            background: green;
            transition: 0.3s;
        }
    </style>
</head>
<body>
    <div id="map"></div>

    <div class="search-container">
         <img src="newlog.png" width="50">
        <input type="text" id="searchInput" placeholder="Search Barangay">
        <button onclick="searchBarangay()">Search</button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.0/papaparse.min.js"></script>
    <script>
        var map = L.map('map').setView([8.2415, 124.6737], 9.4);

        var darkLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var geoJSONLayers = [];

        function loadGeoJSONFiles() {
            const folderPath = './Region10/Barangay/';

            async function getFileNamesInFolder(folderPath) {
                const response = await fetch(folderPath);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const fileLinks = Array.from(doc.querySelectorAll('a')).map(link => link.href);
                const geoJSONFiles = fileLinks.map(link => `./Region10/Barangay/${link.substring(link.lastIndexOf('/') + 1)}`);
                return geoJSONFiles;
            }

            getFileNamesInFolder(folderPath)
                .then(geoJSONFiles => {
                    geoJSONFiles.forEach(url => {
                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                const layer = L.geoJSON(data, {
                                    onEachFeature: function (feature, layer) {
                                        layer.on('click', function () {
                                            // showModal(feature.properties.ADM3_EN, feature.properties.ADM3_PCODE);
                                        });
                                    },
                                    style: function (feature) {
                                        return { fillColor: 'transparent', color: 'blue', weight: 2 };
                                    }
                                });
                                geoJSONLayers.push(layer); // Add layer to the geoJSONLayers array
                                layer.addTo(map);
                            })
                            .catch(error => console.error('Error loading GeoJSON file:', error));
                    });
                })
                .catch(error => console.error('Error retrieving file names:', error));
        }

        loadGeoJSONFiles();

        async function addMarkersFromCSV() {
            const sheetId = '1eHOUCr-trmajZEdksKdLUwWn2tR1ffEqMkkMxASapUU';
            const url = `https://docs.google.com/spreadsheets/d/${sheetId}/gviz/tq?tqx=out:csv`;

            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const text = await response.text();
                const jsonData = Papa.parse(text, { header: true });
                const data = jsonData.data;

                data.forEach(row => {
                    const { EC_ADD, EC_Name, Latitude, EC_Type, Longitude, File_Link } = row;
                    if (!Latitude || !Longitude) {
                        console.warn(`Skipping row due to missing latitude or longitude: ${JSON.stringify(row)}`);
                        return;
                    }

                    const marker = L.marker([parseFloat(Latitude), parseFloat(Longitude)]).addTo(map);

                    const popupContent = `<b>Location: ${EC_ADD}</b><br>Name: ${EC_Name}<br>Type: ${EC_Type}<br>`;

                    marker.on('mouseover', function (e) {
                        const popup = L.popup()
                            .setLatLng(marker.getLatLng())
                            .setContent(popupContent)
                            .openOn(map);
                    });

                    marker.on('mouseout', function (e) {
                        map.closePopup();
                    });

                    marker.on('click', function () {
                        showModal(EC_ADD, EC_Name, File_Link, EC_Type);
                    });
                });
            } catch (error) {
                console.error('Error fetching data from CSV:', error);
            }
        }

        function showModal(EC_ADD, EC_Name, File_Link, EC_Type) {
            const fileIdMatch = File_Link.match(/id=([a-zA-Z0-9_-]+)/);
            const fileId = fileIdMatch ? fileIdMatch[1] : null;
            const embedLink = fileId ? `https://drive.google.com/file/d/${fileId}/preview` : File_Link;

            swal({
                title: `Location: ${EC_ADD}`,
                text: `Name: ${EC_Name}\nType: ${EC_Type}`,
                content: {
                    element: "iframe",
                    attributes: {
                        src: embedLink,
                        width: "100%",
                        height: "500px",
                        frameborder: "0"
                    }
                },
                buttons: {
                    confirm: {
                        text: "Close",
                        value: true,
                        visible: true,
                        className: "btn btn-primary",
                        closeModal: true
                    }
                }
            });
        }

        addMarkersFromCSV();

        function searchBarangay() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();

            // Check if searchInput is empty
            if (searchInput === '') {
                // If empty, reset all feature styles
                geoJSONLayers.forEach(layer => {
                    layer.eachLayer(function (featureLayer) {
                        featureLayer.setStyle({ fillColor: 'transparent', color: 'blue', weight: 2 });
                    });
                });
            } else {
                let boundsGroup = new L.featureGroup();

                // If not empty, run the search logic
                geoJSONLayers.forEach(layer => {
                    layer.eachLayer(function (featureLayer) {
                        const feature = featureLayer.feature;
                        if (feature.properties.ADM4_EN.toLowerCase().includes(searchInput)) {
                            boundsGroup.addLayer(featureLayer);
                            featureLayer.setStyle({ fillColor: 'green', color: 'green', weight: 5 });
                        } else {
                            featureLayer.setStyle({ fillColor: 'transparent', color: 'blue', weight: 2 });
                        }
                    });
                });

                // Fit map to the bounds of all matched features
                if (boundsGroup.getLayers().length > 0) {
                    map.fitBounds(boundsGroup.getBounds());
                }
            }
        }


    </script>
</body>
</html>

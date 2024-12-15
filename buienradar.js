// URL van de Buienradar XML-feed
let buienRadarUrl = 'https://data.buienradar.nl/1.0/feed/xml';

// Functie om data op te halen via fetch
const fetchData = (url) => {
    return new Promise((resolve, reject) => {
        fetch(url)
            .then(response => response.text()) // Haal XML als tekst op
            .then(data => resolve(data))
            .catch(error => reject(error));
    });
};

// Functie om de eerste 5 weerstations te tonen
const showWeatherStations = async () => {
    try {
        // Haal de XML-feed op
        let xmlData = await fetchData(buienRadarUrl);

        // Parse de XML-data
        let parser = new DOMParser();
        let xmlDoc = parser.parseFromString(xmlData, 'application/xml');

        // Selecteer alle weerstations
        let stations = xmlDoc.querySelectorAll('weerstation');

        // Counter voor de weergegeven weerstations
        let count = 0;

        // Loop door de weerstations en toon de eerste 5
        for (const station of stations) {
            if (count >= 5) break; // Stop na 5 stations

            let stationNaam = station.querySelector('stationnaam')?.textContent;
            let temperatuur = station.querySelector('temperatuurGC')?.textContent;
            let windsnelheid = station.querySelector('windsnelheidMS')?.textContent;

            // Maak een lijstitem voor het weerstation
            const listItem = document.createElement('li');
            listItem.textContent = `Station: ${stationNaam}, Temperatuur: ${temperatuur}°C, Windsnelheid: ${windsnelheid} m/s`;

            // Voeg het item toe aan de HTML-lijst
            document.getElementById('weather').appendChild(listItem);

            count++; // Verhoog de teller
        }
    } catch (error) {
        console.error('Er is een fout opgetreden:', error);
    }
};

// Roep de functie aan om de eerste 5 weerstations te tonen
showWeatherStations();

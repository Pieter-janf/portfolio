document.addEventListener('DOMContentLoaded', function () {
    var map = L.map('map').setView([51.1548066, 4.4458312], 13);
  
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap'
    }).addTo(map);
  
    L.marker([51.1548066, 4.4458312]).addTo(map)
      .bindPopup('In deze regio woon ik.')
      .openPopup();
  });
  

// $('.startowa').waypoint(function() {
    
//     //document.getElementById("link0").style.width="150%";
//     //document.getElementById("link0").style.height="150%";

// });


var foty = Array.from(document.getElementsByClassName("galfoto"));
foty.forEach(function(elem) {
    elem.addEventListener("click", function() {
        if(this.style.height != "90vh") this.style.height = "90vh";
        else this.style.height = "300px";
    });
});



    
var mymap = L.map('mapid').setView([50.66739, 17.92602], 20);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(mymap);

L.marker([50.66739, 17.92602]).addTo(mymap)
    .bindPopup('Studio Metamorfoza')
    .openPopup();
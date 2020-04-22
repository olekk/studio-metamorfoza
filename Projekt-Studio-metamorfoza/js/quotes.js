
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


//var htmlStyle = document.getElementById("htmlStyle");


var cover2 = Array.from(document.getElementsByClassName("cover2"));
cover2.forEach(function(elem) {
    elem.addEventListener("click", function() {
        if(this.style.height != "64px") 
        {
            this.style.height = "64px";
            //this.style.border = "0 white solid";
            this.parentNode.childNodes[3].style = "height:"+this.parentNode.childNodes[3].scrollHeight+"px; padding: 90px 50px;";
            //this.parentNode.childNodes[3].style.display = "block";
        }
        else 
        {
            this.style.height = "35vh";
            //this.style.borderBottom = "50px white solid";
            this.parentNode.childNodes[3].style = "height:1px; padding:0 50px;";
            //this.parentNode.childNodes[3].style.display = "none";
        }
    });
});


var cover = Array.from(document.getElementsByClassName("cover"));

cover.forEach(function(elem) {

    elem.style.border = (window.innerWidth<=641 ? "30px white solid" : "100px white solid");

    elem.addEventListener("click", function() {
        //console.log(this.parentNode.childNodes[3].childNodes[19].tagName);
        if(this.style.height != "90px") 
        {
            this.style.height = "90px";
            this.style.border = "0px white solid";
            this.parentNode.style = "min-height: 100vh;";
            //htmlStyle.innerHTML = ".page { min-height: calc(100vh - 70px); }";
            if(this.parentNode.childNodes[3].childNodes[19].tagName == 'SECTION') cover2.forEach(function(elem) {elem.style.position = "absolute"});

        }
        else 
        {
            this.style.height = "100vh";
            this.style.border = (window.innerWidth<=641 ? "30px white solid" : "100px white solid" );
            this.parentNode.style = "height: 100vh;";
            //htmlStyle.innerHTML = ".page { height: calc(100vh - 70px); }";
            if(this.parentNode.childNodes[3].childNodes[19].tagName == 'SECTION') cover2.forEach(function(elem) {elem.style.position = "static"});
        }
    });
});

var lista = Array.from(document.getElementsByClassName("lista-rozw"));
lista.forEach(function(elem) {
    elem.addEventListener("click", function() {
        if(Array.from(this.childNodes)[3].style.display != "block")
        {
            Array.from(this.childNodes)[3].style.display = "block"
        }
        else Array.from(this.childNodes)[3].style.display = "none";
    });
});


    
var mymap = L.map('mapid').setView([50.66739, 17.92602], 20);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors'
}).addTo(mymap);

L.marker([50.66739, 17.92602]).addTo(mymap)
    .bindPopup('Studio Metamorfoza')
    .openPopup();








    var gallery = $('.gallery a').simpleLightbox();

        $.simpleLightbox({

            // default source attribute
            sourceAttr: 'href',

            // shows fullscreen overlay
            overlay: true,

            // shows loading spinner
            spinner: true,

            // shows navigation arrows
            nav: true,

            // text for navigation arrows
            navText: ['&larr;', '&rarr;'],

            // shows image captions
            captions: true,
            captionDelay: 0,
            captionSelector: 'img',
            captionType: 'attr',
            captionPosition: 'bottom',
            captionClass: '',

            // captions attribute (title or data-title)
            captionsData: 'title',

            // shows close button
            close: true,

            // text for close button
            closeText: 'X',

            // swipe up or down to close gallery
            swipeClose: true,

            // show counter
            showCounter: true,

            // file extensions
            fileExt: 'png|jpg|jpeg|gif',

            // weather to slide in new photos or not, disable to fade
            animationSlide: true,

            // animation speed in ms
            animationSpeed: 250,

            // image preloading
            preloading: true,

            // keyboard navigation
            enableKeyboard: true,

            // endless looping
            loop: true,

            // group images by rel attribute of link with same selector
            rel: false,

            // closes the lightbox when clicking outside
            docClose: true,

            // how much pixel you have to swipe
            swipeTolerance: 50,

            // lightbox wrapper Class
            className: 'simple-lightbox',

            // width / height ratios
            widthRatio: 0.8,
            heightRatio: 0.9,

            // scales the image up to the defined ratio size
            scaleImageToRatio: false,

            // disable right click
            disableRightClick: false,

            // disable page scroll
            //disable < a href = "https://www.jqueryscript.net/tags.php?/Scroll/" > Scroll < /a>:    true,

            // show an alert if image was not found
            alertError: true,

            // alert message
            alertErrorMessage: 'Image not found, next image will be loaded',

            // additional HTML showing inside every image
            additionalHtml: false,

            // enable history back closes lightbox instead of reloading the page
            history: true,

            // time to wait between slides
            throttleInterval: 0,

            // Pinch to <a href="https://www.jqueryscript.net/zoom/">Zoom</a> feature for touch devices
            doubleTapZoom: 2,
            maxZoom: 10

        });
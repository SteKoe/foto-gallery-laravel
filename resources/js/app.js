import './bootstrap';
import lightGallery from "lightgallery";
import lgZoom from "lightgallery/plugins/zoom";
import lgThumbnail from "lightgallery/plugins/thumbnail";

import "lightgallery/css/lightgallery.css"
import "lightgallery/css/lg-medium-zoom.css"
import "lightgallery/css/lg-thumbnail.css"

window.addEventListener('scroll', function () {
    let classList = document.querySelector('nav.navigation').classList;
    let classesToAdd = 'scrolled';
    if (window.scrollY > 10) {
        classList.add(classesToAdd);
    } else {
        classList.remove(classesToAdd);
    }
});

lightGallery(document.querySelector('.page-gallery .gallery-images'), {
    plugins: [lgZoom, lgThumbnail],
    pager: true,
    speed: 500,
    // ... other settings
});

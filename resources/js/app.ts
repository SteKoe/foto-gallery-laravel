import './bootstrap';
import lightGallery from "lightgallery";
import lgZoom from "lightgallery/plugins/zoom";
import lgThumbnail from "lightgallery/plugins/thumbnail";

import "lightgallery/css/lightgallery.css"
import "lightgallery/css/lg-medium-zoom.css"
import "lightgallery/css/lg-thumbnail.css"

const classList = document.querySelector('nav.navigation').classList;
const toTopLink = document.querySelector('#to-top').classList
const classesToAdd = 'scrolled';
window.addEventListener('scroll', function () {
    let currentScroll = window.scrollY;

    if (currentScroll > 49.5) {
        classList.add(classesToAdd);
    } else {
        classList.remove(classesToAdd);
    }

    if (currentScroll > 250) {
        toTopLink.remove('opacity-0');
        toTopLink.add('opacity-100');
    } else {
        toTopLink.add('opacity-0');
        toTopLink.remove('opacity-100');
    }
});

lightGallery(document.querySelector('.page-gallery .gallery-images'), {
    plugins: [lgZoom, lgThumbnail],
    pager: true,
    speed: 500,
});

import lightGallery from "lightgallery";

import lgZoom from "lightgallery/plugins/zoom";
import lgThumbnail from "lightgallery/plugins/thumbnail";
import lgHash from "lightgallery/plugins/hash";

import "lightgallery/css/lightgallery.css"
import "lightgallery/css/lg-medium-zoom.css"
import "lightgallery/css/lg-thumbnail.css"

lightGallery(document.querySelector('.page-gallery .gallery-images'), {
    plugins: [lgZoom, lgThumbnail, lgHash],
    closable: true,
    speed: 500,
    strings: {
        closeGallery: 'Schließen',
        toggleMaximize: 'Maximieren',
        previousSlide: 'Vorheriges Bild',
        nextSlide: 'Nächstes Bild',
        download: 'Herunterladen',
        playVideo: 'Video abspielen',
        mediaLoadingFailed: 'Oha... Da ist etwas schief gelaufen!',
    }
});

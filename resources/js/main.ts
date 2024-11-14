const classList = document.querySelector('nav.navigation').classList;
const toTopLink = document.querySelector('#to-top').classList
const classesToAdd = 'scrolled';

window.addEventListener('scroll', function () {
    let threshold = 50;
    let currentScroll = document.body.scrollTop > threshold || document.documentElement.scrollTop > threshold;

    if (currentScroll) {
        classList.add(classesToAdd);

        toTopLink.remove('opacity-0');
        toTopLink.add('opacity-100');
    } else {
        classList.remove(classesToAdd);

        toTopLink.add('opacity-0');
        toTopLink.remove('opacity-100');
    }
});

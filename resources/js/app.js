import './bootstrap';

window.addEventListener('scroll', function () {
    let classList = document.querySelector('nav.navigation').classList;
    let classesToAdd = 'scrolled';
    if (window.scrollY > 10) {
        classList.add(classesToAdd);
    } else {
        classList.remove(classesToAdd);
    }
});

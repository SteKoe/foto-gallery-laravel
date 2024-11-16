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

const lightSwitches = document.querySelectorAll('.light-switch');
console.log(lightSwitches);
if (lightSwitches.length > 0) {
    lightSwitches.forEach((lightSwitch: HTMLInputElement, i) => {
        if (localStorage.getItem('dark-mode') === 'true') {
            lightSwitch.checked = true;
        }
        lightSwitch.addEventListener('change', () => {
            console.log("CHANGE")
            const {checked} = lightSwitch;
            lightSwitches.forEach((el: HTMLInputElement, n) => {
                if (n !== i) {
                    el.checked = checked;
                }
            });
            if (lightSwitch.checked) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('dark-mode', JSON.stringify(true));
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('dark-mode', JSON.stringify(false));
            }
        });
    });
}

if (!localStorage.getItem('dark-mode')) {
    localStorage.setItem('dark-mode', JSON.stringify(true));
}

if (localStorage.getItem('dark-mode') === 'true' || (!('dark-mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.querySelector('html').classList.add('dark');
} else {
    document.querySelector('html').classList.remove('dark');
}

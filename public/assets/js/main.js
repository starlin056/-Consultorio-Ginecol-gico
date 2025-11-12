// Funciones globales del sistema
document.addEventListener('DOMContentLoaded', function() {
    initTooltips();
    initSearch();
    initForms();
    initComponents();
    initLightningNav();
});

/* ----------------------- */
/* ✅ NAVBAR CON EFECTO RAYO */
/* ----------------------- */

function initLightningNav() {
    const navElement = document.querySelector('.nav-menu');
    if (!navElement) return;

    const navLinks = document.querySelectorAll('.nav-link');
    if (navLinks.length === 0) return;

    const activeElement = document.createElement('div');
    activeElement.classList.add('active-element');
    navElement.appendChild(activeElement);

    const getOffsetLeft = (element) => {
        const elementRect = element.getBoundingClientRect();
        const navRect = navElement.getBoundingClientRect();
        return elementRect.left - navRect.left + (elementRect.width - activeElement.offsetWidth) / 2;
    };

    const currentPath = window.location.pathname;
    let activeButton = null;

    navLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        if (currentPath.includes(linkHref.replace(BASE_URL, '')) && linkHref !== BASE_URL + '/') {
            link.closest('.nav-item').classList.add('active');
            activeButton = link;
        }
    });

    if (!activeButton) {
        activeButton = navLinks[0];
        navLinks[0].closest('.nav-item').classList.add('active');
    }

    document.fonts.ready.then(() => {
        gsap.set(activeElement, { x: getOffsetLeft(activeButton) });
        gsap.to(activeElement, { '--active-element-show': '1', duration: 0.2 });
    });

    navLinks.forEach((link, index) => {
        link.closest('.nav-item').addEventListener('click', (e) => {
            e.preventDefault();
            const active = navElement.querySelector('.nav-item.active');
            const oldIndex = [...navElement.querySelectorAll('.nav-item')].indexOf(active);

            if (index === oldIndex || navElement.classList.contains('before') || navElement.classList.contains('after')) {
                window.location.href = link.getAttribute('href');
                return;
            }

            const x = getOffsetLeft(link);
            const direction = index > oldIndex ? 'after' : 'before';
            const spacing = Math.abs(x - getOffsetLeft(active));

            navElement.classList.add(direction);
            active.classList.remove('active');
            link.closest('.nav-item').classList.add('active');

            gsap.set(activeElement, { rotateY: direction === 'before' ? '180deg' : '0deg' });

            gsap.to(activeElement, {
                keyframes: [
                    {
                        '--active-element-width': `${spacing > navElement.offsetWidth - 60 ? navElement.offsetWidth - 60 : spacing}px`,
                        duration: 0.3,
                        ease: 'none',
                        onStart: () => {
                            createSVG(activeElement);
                            gsap.to(activeElement, { '--active-element-opacity': 1, duration: 0.1 });
                        }
                    },
                    {
                        '--active-element-scale-x': '0',
                        '--active-element-scale-y': '.25',
                        '--active-element-width': '0px',
                        duration: 0.3,
                        onStart: () => {
                            gsap.to(activeElement, { '--active-element-mask-position': '40%', duration: 0.5 });
                            gsap.to(activeElement, { '--active-element-opacity': 0, delay: 0.45, duration: 0.25 });
                        },
                        onComplete: () => {
                            activeElement.innerHTML = '';
                            navElement.classList.remove('before', 'after');
                            activeElement.removeAttribute('style');
                            gsap.set(activeElement, { x: getOffsetLeft(link), '--active-element-show': '1' });

                            setTimeout(() => {
                                window.location.href = link.getAttribute('href');
                            }, 100);
                        }
                    }
                ]
            });

            gsap.to(activeElement, { x, '--active-element-strike-x': '-50%', duration: 0.6, ease: 'none' });
        });
    });

    navLinks.forEach(link => {
        link.addEventListener('mouseenter', () => {
            if (!link.closest('.nav-item').classList.contains('active')) {
                gsap.to(link, { color: getComputedStyle(document.documentElement).getPropertyValue('--primary').trim(), duration: 0.2 });
                gsap.to(link.querySelector('i'), { color: getComputedStyle(document.documentElement).getPropertyValue('--primary').trim(), duration: 0.2 });
            }
        });
        link.addEventListener('mouseleave', () => {
            if (!link.closest('.nav-item').classList.contains('active')) {
                gsap.to(link, { color: getComputedStyle(document.documentElement).getPropertyValue('--text-light').trim(), duration: 0.2 });
                gsap.to(link.querySelector('i'), { color: getComputedStyle(document.documentElement).getPropertyValue('--text-light').trim(), duration: 0.2 });
            }
        });
    });
}

/* ----------------------- */
/* ✅ FUNCIONES FORMULARIOS */
function actualizarRequeridos() {
    const tipoMedicamento = document.getElementById("tipo_medicamento").checked;
    const camposMedicamentos = document.querySelectorAll('#medicamentosContainer [required]');
    const camposAnalisis = document.querySelectorAll('#analisisContainer [required]');

    if (tipoMedicamento) {
        camposMedicamentos.forEach(campo => campo.setAttribute('required', ''));
        camposAnalisis.forEach(campo => campo.removeAttribute('required'));
    } else {
        camposAnalisis.forEach(campo => campo.setAttribute('required', ''));
        camposMedicamentos.forEach(campo => campo.removeAttribute('required'));
    }
}

function cambiarTipoReceta() {
    const tipoMedicamento = document.getElementById("tipo_medicamento").checked;
    const seccionMedicamentos = document.getElementById("seccionMedicamentos");
    const seccionAnalisis = document.getElementById("seccionAnalisis");
    const camposMedicamentos = document.querySelectorAll('#medicamentosContainer [required]');
    const camposAnalisis = document.querySelectorAll('#analisisContainer [required]');

    if (tipoMedicamento) {
        seccionMedicamentos.style.display = "block";
        seccionAnalisis.style.display = "none";
        camposMedicamentos.forEach(campo => campo.setAttribute('required', ''));
        camposAnalisis.forEach(campo => campo.removeAttribute('required'));
    } else {
        seccionMedicamentos.style.display = "none";
        seccionAnalisis.style.display = "block";
        camposAnalisis.forEach(campo => campo.setAttribute('required', ''));
        camposMedicamentos.forEach(campo => campo.removeAttribute('required'));
    }
}

/* ----------------------- */
/* ✅ FUNCION CREAR SVG */
const createSVG = (element) => {
    element.innerHTML = `
    <svg viewBox="0 0 116 5" preserveAspectRatio="none" class="beam">
      <path d="M0.5 2.5L113 0.534929C114.099 0.515738 115 1.40113 115 2.5C115 3.59887 114.099 4.48426 113 4.46507L0.5 2.5Z" fill="url(#gradient-beam)"/>
      <defs>
        <linearGradient id="gradient-beam" x1="2" y1="2.5" x2="115" y2="2.5" gradientUnits="userSpaceOnUse">
          <stop stop-color="#a78bfa"/>
          <stop offset="1" stop-color="#f472b6"/>
        </linearGradient>
      </defs>
    </svg>
    <div class="strike"></div>
    `;
};

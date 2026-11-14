// Validación rápida antes de enviar. La contraseña la revisa PHP.
const formulario = document.getElementById('formulario-inicio');
const mensaje = document.getElementById('mensaje');
const campoUsuario = document.getElementById('usuario');
const campoContrasena = document.getElementById('password');

if (formulario) {
    formulario.addEventListener('submit', evento => {
        if (!campoUsuario.value.trim() || !campoContrasena.value) {
            evento.preventDefault();
            mensaje.textContent = 'Completá usuario y contraseña.';
        }
    });
}

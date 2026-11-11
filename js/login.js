// Validación visual sencilla. PHP realiza la autenticación real.
const formulario = document.getElementById("formulario-inicio");
const mensaje = document.getElementById("mensaje");
formulario?.addEventListener("submit", (evento) => {
  const usuario = document.getElementById("usuario").value.trim();
  const password = document.getElementById("contrasena").value;
  if (!usuario || !password) {
    evento.preventDefault();
    mensaje.textContent = "Completá usuario y contraseña.";
  }
});
if (new URLSearchParams(location.search).has("error")) {
  mensaje.textContent = "No se pudo iniciar sesión. Revisá los datos.";
}

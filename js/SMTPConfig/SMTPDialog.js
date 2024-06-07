import { saveSTMPConfig, getSTMPConfig } from "./utils/fetch.js";
import { sendEmailWithWaitingTimeReports, sendEmailToAllApiUsers, sendEmailToAllUsers } from "../mailSender/utils/fetch.js";
/**
 * #smtp_host
 * #smtp_port"
 * #smtp_username"
 * #smtp_password"
 * #smtp_encryption"
 * #smtp_auth_method"
 * @returns
 */
function SMTPDialog(config) {
  const { host, port, username, pass, encryp, auth_method } = config;
  return `
  <form id="SMTPForm">
   
   <label for="smtp_host">Host SMTP:</label><br>
   <input value="${host}" class="form-control campoConfig" type="text" id="smtp_host" name="smtp_host"><br>

   <label for="smtp_port">Puerto SMTP:</label><br>
   <input value="${port}" class="form-control campoConfig" type="number" id="smtp_port" name="smtp_port"><br>

   <label for="smtp_username">Usuario SMTP:</label><br>
   <input value="${username}" class="form-control campoConfig" type="text" id="smtp_username" name="smtp_username"><br>
   
   <label for="smtp_pass">Contraseña SMTP:</label><br>
   <input value="${pass}" class="form-control campoConfig" type="password" id="smtp_pass" name="smtp_pass">
   <div style="display: inline-block" id="btn_toggle_pass_SMTP" onclick ="toggleSMTPPass()">
    <i class="fas fa-eye"></i>
   </div><br>
   
   <label for="smtp_encryp">Modo de encriptación:</label><br>
   <select class="form-control campoConfig" id="smtp_encryp" name="smtp_encryp">
    <option id="tls" value="tls" ${
      encryp == "tls" ? "selected" : ""
    }>TLS</option>
    <option id="ssl" value="ssl"  ${
      encryp == "ssl" ? "selected" : ""
    }>SSL</option>
    <option id="none" value="none"  ${
      encryp == "none" ? "selected" : ""
    }>None</option>
   </select><br>

   <label for="smtp_auth_method">Método de auntentificación:</label><br>
   <select class="form-control campoConfig" id="smtp_auth_method" name="smtp_auth_method">
    <option id="plain" value="plain" ${
      auth_method == "plain" ? "selected" : ""
    }>Plain</option>
    <option id="login" value="login" ${
      auth_method == "login" ? "selected" : ""
    }>Login</option>
    <option id="cram-md5" value="cram-md5" ${
      auth_method == "cram-md5" ? "selected" : ""
    }>CRAM-MD5</option>
   </select><br><br>

   </form>
   `;
}

function submitSMTPForm() {
  const smtpConfig = {
    //el id siempre es 1
    id: 1,
    host: $("#smtp_host").val(),
    port: $("#smtp_port").val(),
    username: $("#smtp_username").val(),
    pass: $("#smtp_pass").val(),
    encryp: $("#smtp_encryp").val(),
    auth_method: $("#smtp_auth_method").val(),
  };

  const isValid = validateSMTPForm(smtpConfig);

  if (isValid) {
    saveSTMPConfig(smtpConfig);
    return true;
  }

  return false;
}

function validateSMTPForm({ host, port, username, pass, encryp, auth_method }) {
  if (host == "") {
    console.log("HOST NO válido");
    $.alert("Introduce un HOST válido");
    return false;
  }
  if (port == "" || port == 0) {
    console.log("PUERTO NO válido");
    $.alert("Introduce un PUERTO válido");
    return false;
  }
  if (username == "") {
    console.log("USUARIO NO válido");
    $.alert("Introduce un USUARIO válido");
    return false;
  }
  if (pass == "") {
    console.log("CONTRASEÑA NO válido");
    $.alert("Introduce una CONTRASEÑA válida");
    return false;
  }
  console.log("Formulario SMTP válido");
  return true;
}

async function showDialog() {
  const config = await getSTMPConfig();
  const content = SMTPDialog(config);
  $.confirm({
    title: "Configuración SMTP",
    content: content,
    buttons: {
      // ok: {
      //   text: "Prueba",
      //   action: () => {
      //     //sendEmailToAllApiUsers();
      //     //sendEmailToAllUsers();
      //     sendEmailWithWaitingTimeReports();
      //   },
      // },
      cancel: {
        text: "Cancelar",
        action: () => {},
      },
      formSubmit: {
        text: "Guardar",
        action: () => {
          return submitSMTPForm();
        },
      },
    },
    // Clase de columna para ajustar el ancho del cuadro de diálogo
    //columnClass: "small",
    // Hace que el cuadro de diálogo ocupe todo el ancho de la ventana
    //containerFluid: true,
    // Evita que el cuadro de diálogo se cierre haciendo clic fuera de él
    //backgroundDismiss: false,
  });
}

export { showDialog };

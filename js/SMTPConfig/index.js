import { showDialog } from "./SMTPDialog.js";
import { initSMTP } from "./utils/fetch.js";
//Para inicializar la config del SMTP, se guarda la config en una var de SESSION de PHP
initSMTP();

$("#btnSMTPConfig").on("click", function () {
  showDialog();
});

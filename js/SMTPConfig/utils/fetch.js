import {
  ACTION_GET_SMTP_CONFIG,
  ACTION_SAVE_SMTP_CONFIG,
  ACTION_INIT_SMTP,
} from "./actions.js";

async function getSTMPConfig() {
  const data = {
    action: ACTION_GET_SMTP_CONFIG,
  };

  try {
    const response = await fetch("./controllers/SMTPConfigController.php", {
      method: "POST",
      body: JSON.stringify(data),
    });

    const json = await response.json();
    return json.data;
  } catch (error) {
    console.log(error);
    return null;
  }
}

/**
 * Inicializa la configuracion del PHPMailer para la creacion de email atraves de guardar en cache una variable de session
 * $SESSION["smtp_config"]
 */
async function initSMTP() {
  const data = {
    action: ACTION_INIT_SMTP,
  };

  try {
    const response = await fetch("./controllers/SMTPConfigController.php", {
      method: "POST",
      body: JSON.stringify(data),
    });

    const responseText = await response.text();

    console.log(responseText);

  } catch (error) {
    console.log(error);
  }
}

async function saveSTMPConfig(config) {
  console.log("Enviando nueva SMTP Config");
  const data = {
    action: ACTION_SAVE_SMTP_CONFIG,
    smtpConfig: config,
  };
  try {
    const response = await fetch("./controllers/SMTPConfigController.php", {
      method: "POST",
      body: JSON.stringify(data),
    });
    const json = await response.json();
    console.log(json);
  } catch (error) {
    console.log(error);
  }

  //Despues de guardar la nueva smtp_config se actualiza la variable de session de PHP
  initSMTP();
}

export { getSTMPConfig, saveSTMPConfig, initSMTP };

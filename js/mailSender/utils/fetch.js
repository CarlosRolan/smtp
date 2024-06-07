import {
  ACTION_SEND_EMAIL,
  ACTION_SEND_EMAIL_ALL_USERS,
  ACTION_SEND_EMAIL_ALL_API_USERS,
  ACTION_SEND_EMAIL_WAITING_TIME_REPORTS,
} from "./actions.js";

async function sendEmailTo(email, msg_title) {
  const data = {
    action: ACTION_SEND_EMAIL,
    address: email,
    title: msg_title,
  };

  try {
    const response = await fetch("./controllers/MailerController.php", {
      method: "POST",
      body: JSON.stringify(data),
    });
    const json = await response.json();
    console.log(json);
  } catch (error) {
    console.log(error);
  }
}

async function sendEmailToAllUsers() {
  const data = {
    action: ACTION_SEND_EMAIL_ALL_USERS,
  };

  try {
    const response = await fetch("./controllers/MailerController.php", {
      method: "POST",
      body: JSON.stringify(data),
    });
    const responseText = await response.text();

    console.log(responseText);

  } catch (error) {
    console.log(error);
  }
}

async function sendEmailToAllApiUsers() {
  const data = {
    action: ACTION_SEND_EMAIL_ALL_API_USERS,
  };

  try {
    const response = await fetch("./controllers/MailerController.php", {
      method: "POST",
      body: JSON.stringify(data),
    });
    const responseText = await response.text();

    console.log(responseText);

  } catch (error) {
    console.log(error);
  }
}

async function sendEmailWithWaitingTimeReports() {
  const data = {
    action: ACTION_SEND_EMAIL_WAITING_TIME_REPORTS,
  };

  //./generarArchivoEspera.php
  //"./controllers/MailerController.php"
  try {
    const response = await fetch("./controllers/MailerController.php", {
      method: "POST",
      body: JSON.stringify(data),
    });
    const responseText = await response.text();

    console.log(responseText);

  } catch (error) {
    console.log(error);
  }
}

export {
  sendEmailTo,
  sendEmailToAllUsers,
  sendEmailToAllApiUsers,
  sendEmailWithWaitingTimeReports,
};

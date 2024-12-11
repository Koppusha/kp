

function showCustomAlert(title = "Уведомление", message = "Сообщение") {
    const alertBox = document.getElementById('customAlert');

    document.querySelector('.custom-alert-title').textContent = title;
    document.querySelector('.custom-alert-body').textContent = message;

    alertBox.style.display = 'block'; 
    setTimeout(() => {
        alertBox.style.top = '20px'; 
        alertBox.style.opacity = '1'; 
    }, 100); 

    setTimeout(() => {
        closeCustomAlert();
    }, 2000);
}

function closeCustomAlert() {
    const alertBox = document.getElementById('customAlert');
    alertBox.style.top = '-100px'; 
    alertBox.style.opacity = '0'; 
    setTimeout(() => {
        alertBox.style.display = 'none'; 
    }, 500); 
}

function showAllNotifications(notifications) {
    let i = 0;

    function showNextNotification() {
        if (i < notifications.length) {
            const { title, message } = notifications[i];
            showCustomAlert(title, message);
            i++;

            setTimeout(showNextNotification, 3500); 
        }
    }

    showNextNotification();
}


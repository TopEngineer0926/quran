// request permission on page load
document.addEventListener('DOMContentLoaded', function () {
    if (!Notification) {
        alert('Desktop notifications not available in your browser. Try Chromium.');
        return;
    }

    if (Notification.permission !== 'granted')
        Notification.requestPermission();
});

function notifyMe(title,body) {
    if (document.hidden) {
        if (Notification.permission !== 'granted')
            Notification.requestPermission();
        else {
            var notification = new Notification(title, {
                icon: 'https://timesofislamabad.com/digital_images/large/2016-06-20/nha-introduces-new-technologies-and-non-conventional-materials-in-pakistan-1513927112-3985.jpg',
                body: body,
            });

            notification.onclick = function () {
                window.focus();
                notification.close();
            };
        }
    }
}

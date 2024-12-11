
var swiper = new Swiper('.swiper-container', {
    loop: true,
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
});

const modal = document.getElementById('applicationModal');
const closeBtn = document.getElementsByClassName('close')[0];

function openApplicationForm(offerId, creditName, bankName) {
    document.getElementById('bankName').value = bankName;
    document.getElementById('creditName').value = creditName;
    document.getElementById('offerId').value = offerId
    modal.style.display = 'flex';
}

closeBtn.onclick = function () {
    modal.style.display = 'none';
}

window.onclick = function (event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

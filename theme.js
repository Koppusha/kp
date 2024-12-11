function getCookie(name) {

    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : null;
}

function setTheme(theme) {
    document.body.className = theme;
}

window.addEventListener("load", () => {
    applyTheme();
});

function applyTheme() {
    var theme = getCookie("theme") || "light";
    if(theme === "efec50a51a1112b721a2472aa6eea65f827d59420a27d892bac878aa34517d52"){
        theme = "light"
    }else if(theme === "3d1b6e5178bcb728f9ddc6c1fdc0a1d809f88f707c0566dcbc1206ee5fcc5d61"){
        theme= "dark"
    }
    setTheme(theme);
}
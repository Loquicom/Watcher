/* --- Cookies --- */

function getCookie(key) {
    const split = document.cookie.split(';');
    let cookies = {};
    split.forEach(elt => {
        const val = elt.trim().split('=');
        cookies[val[0]] = val[1];
    });
    if(key !== undefined) {
        return cookies[key];
    }
    return cookies;
}

function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

function eraseCookie(name) {   
    document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

/* --- Listeners --- */

function switchTheme() {
    if ($(this).prop('checked')) {
        $('html').attr('data-theme', 'dark');
        setCookie('theme', 'dark', 365);
    } else {
        $('html').attr('data-theme', 'light');
        setCookie('theme', 'light', 365);
    }
}

function switchTab(event, tab) {
    const zone = tab || $(this).attr('id')?.replace('menu-', '');
    $('.content').addClass('hide');
    $('#' + zone).removeClass('hide');
    $('.selected').removeClass('selected');
    $('#menu-' + zone).addClass('selected');
    window.location.hash = zone;
    $("html, body").animate({ scrollTop: 0 }, 0);
}

function switchOverview() {
    const page = $(this).attr('id')?.replace('-overview', '');
    $('#menu-' + page).click();
}

/* --- Loader --- */

function loadTheme() {
    const theme = getCookie('theme');
    if (theme !== undefined) {
        $('html').attr('data-theme', theme);
        if (theme === 'dark') {
            $('#switch-theme').prop('checked', true);
        }
    }
}

function loadTab() {
    const zone = window.location.hash;
    if (zone.trim() !== '') {
        switchTab(null, zone.replace('#', ''));
    }
}

/* --- Ready --- */

$(() => {
    // Chargement du bon theme
    loadTheme();
    // Chargement bon onglet
    loadTab();
    // Gestion changement de theme
    $('#switch-theme').on('change', switchTheme);
    // Changement onglet
    $('.menu').on('click', switchTab);
    $('.overview-title').on('click', switchOverview);
});
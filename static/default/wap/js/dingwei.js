var baolock = 1;
var baonum = 1;
var myScroll = null;



function dingwei(page, lat, lng) {
    page = page.replace('llaatt', lat);
    page = page.replace('llnngg', lng);
    $.get(page, function (data) {
    }, 'html');
}

/* 公用 */

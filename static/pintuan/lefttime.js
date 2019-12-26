function timer(intDiff){
    window.setInterval(function(){
    var hour=0,
        minute=0,
        second=0;  
    if(intDiff > 0){
        hour = Math.floor(intDiff / (60 * 60));
        minute = Math.floor(intDiff / 60) - (hour * 60);
        second = Math.floor(intDiff) - (hour * 60 * 60) - (minute * 60);
    }
    if (minute <= 9) minute = '0' + minute;
    if (second <= 9) second = '0' + second;
    $('#ti_time_hour').html(hour+'');
    $('#ti_time_min').html(minute+'');
    $('#ti_time_sec').html(second+'');
    intDiff--;
    }, 1000);
} 
$(function(){
    timer(intDiff);
});
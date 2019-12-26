//随着屏幕滚动
jQuery.fn.imageScroller = function(params){
    var p = params || {
        next:"buttonNext",
        prev:"buttonPrev",
        frame:"viewerFrame",
        scrolltime:2000,
        width:100,
        child:"a",
        auto:true
    }; 
    var _btnNext = $("#"+ p.next);
    var _btnPrev = $("#"+ p.prev);
    var _imgFrame = $("#"+ p.frame);
    var _width = p.width;
    var _height = p.height;
    var _child = p.child;
    var _auto = p.auto;
    var _itv;
    var _scrolltime=p.scrolltime;
    var _turndirection=p.turndirection;
    var turnLeft = function(){
        _btnPrev.unbind("click",turnLeft);
        if(_auto) autoStop();
        _imgFrame.animate( {
            marginLeft:-_width
            }, 'slow', '', function(){
            _imgFrame.find(_child+":first").appendTo( _imgFrame );
            _imgFrame.css("marginLeft",0);
            _btnPrev.bind("click",turnLeft);
            if(_auto) autoPlay();
        });
    };
    var turnRight = function(){
        _btnNext.unbind("click",turnRight);
        if(_auto) autoStop();
        _imgFrame.find(_child+":last").clone().show().prependTo( _imgFrame );
        _imgFrame.css("marginLeft",-_width);
        _imgFrame.animate( {
            marginLeft:0
        }, 'slow' ,'', function(){
            _imgFrame.find(_child+":last").remove();
            _btnNext.bind("click",turnRight);
            if(_auto) autoPlay(); 
        });
    };
    _btnNext.css("cursor","hand").click( turnRight );
    _btnPrev.css("cursor","hand").click( turnLeft );
    var autoPlay = function(){
        _itv = window.setInterval(turnLeft, _scrolltime);
    };
    var autoTopPlay = function(){
        _itv = window.setInterval(turnTop, _scrolltime);
    };
    var autoStop = function(){
        window.clearInterval(_itv);
    };
    /**自动滚动**/
    if(_auto && _turndirection=='turnTop'){
        autoTopPlay();
    }else{
        autoPlay();
    }
/**自动滚动**/
};
var baolock = 1;
var baonum = 1;
function showLoader(msg) {
    $(".bao_loading").html(msg).show();
}

function hideLoader()
{
    $(".bao_loading").hide();
}

function dingwei(page,lat,lng){
    page = page.replace('llaatt',lat);
    page = page.replace('llnngg',lng);
    $.get(page,function(data){        
    },'html');
}


function loaddata(page,obj,sc){
    var link = page.replace('0000',baonum);
    showLoader('正在加载中....');
    
    $.get(link,function(data){
        if(data != 0){
            obj.append(data);              
        }
        baolock = 0;
        hideLoader();
    },'html');
    if(sc === true){
        $(window).scroll(function(){              
            if(!baolock && $(window).scrollTop() ==$(document).height() - $(window).height()  ){
                baolock = 1;
                baonum++;
                var link = page.replace('0000',baonum);
                showLoader('正在为客官探路');
                $.get(link,function(data){
                    if(data != 0){
                        obj.append(data);               
                    } 
                    baolock = 0;         
                    hideLoader();
                },'html');
            }           
        });
    }
}
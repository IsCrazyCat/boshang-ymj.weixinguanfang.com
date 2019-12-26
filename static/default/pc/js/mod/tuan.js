$(document).ready(function(){
	$("#jq-jia").click(function(){
		var num = $("#jq-num").val();
		if (num < 99){
			num++;
		}
		$("#jq-num").val(num);
	});
	
	$("#jq-jian").click(function(){
		var num = $("#jq-num").val();
			if (num > 1){
				num--;
			}
		$("#jq-num").val(num);
	});
});

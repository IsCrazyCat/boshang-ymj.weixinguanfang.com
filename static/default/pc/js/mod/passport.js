	var mobile_timeout;
	var mobile_count = 100;
	var mobile_lock = 0;
	$(function () {

		$("#m_zcyz").click(function () {
			if (mobile_lock == 0) {
			mobile_lock = 1;
			$.ajax({
				url: '/passport/sendsms.html',
				data: 'mobile=' + $("#mobile").val(),
				type: 'post',
				success: function (data) {
				if (data == 1) {
				// alert(data);
					mobile_count = 60;
					BtnCount();
				} else {
					mobile_lock = 0;
					error(data);
						}
					}
				});
			}
		});
	});
	
	BtnCount = function () {
	if (mobile_count == 0) {
		$('#m_zcyz').html("重新发送");
			mobile_lock = 0;
			clearTimeout(mobile_timeout);
		}
	else {
		mobile_count--;
		$('#m_zcyz').html("获取(" + mobile_count.toString() + ")秒");
		mobile_timeout = setTimeout(BtnCount, 1000);
		}
	};
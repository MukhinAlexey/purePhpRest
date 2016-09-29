$('.message a').click(function(){
   $("#info").empty();
   $('form').animate({height: "toggle", opacity: "toggle"}, "slow");
});

$(document).ready(function(){
	$('#login').click(function(){
		var login = $('#login-login').val();
		var password = $('#login-password').val();

		if ($.trim(login).length > 0 && $.trim(password).length > 5){
			$.ajax({
				url:"../api.php?action=login",
				method:"POST",
				data:{
					login:login,
					password:password,
				},
				dataType: 'json',
				cache:false,
				success:function(data){
					$("#info").empty();
					$("#info").append("<p>Выполняется вход в систему</p>");	  
					localStorage.setItem('token', data['access_token']);
					window.location="index.php"; 
				},
				error:function(){
					$("#info").empty();
					$("#info").append("<p>Неверные имя пользователя или пароль</p>");	  
				}
			});
		}
		else{
			$("#info").empty();
			$("#info").append("<p>Поля не должны быть пустыми. Пароль должен быть не меньше 6 символов.</p>");	
			return false;
		}
	});
});

$(document).ready(function(){
	$('#register').click(function(){
		var login = $('#registration-login').val();
		var password = $('#registration-password').val();
		var email = $('#registration-email').val();

		if ($.trim(login).length > 0 && $.trim(password).length > 5){
			$.ajax({
				url:"../api.php?action=register",
				method:"POST",
				data:{
					login:login,
					password:password,
					email:email
				},
				cache:false,
				success:function(data){
					if(data){
						console.log(data);
					}
				}
			});
		}
		else{
			$("#info").empty();
			$("#info").append("<p>Поля не должны быть пустыми. Пароль должен быть не меньше 6 символов.</p>");	
		}

	});
});
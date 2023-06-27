$(document).ready(function(){
	var loginObj = new loginForm();

	$(document).on('click', '.login-submit-btn', function(e){
		e.preventDefault();
		loginObj.formSubmit();
	});
});

function loginForm(){
	this.apiURL = 'https://api.homeoftraining.com';
	this.webURL = 'https://lms.homeoftraining.com/#'
	this.emailFieldSelector = $('#login_email');
	this.passwordFieldSelector = $('#login_password');
	this.formId = $('#login-form');
	this.formData = {};
}

loginForm.prototype.formValidation = function() {
	this.error = false;
	if(this.formData.email.length === 0){
		this.emailFieldSelector.css("border-color", "red");
		this.error = true;
	}else{
		this.emailFieldSelector.css("border-color", "");
	}
	if(this.formData.password.length === 0){
		this.passwordFieldSelector.css("border-color", "red");
		this.error = true;
	}else{
		this.passwordFieldSelector.css("border-color", "");
	}
};

loginForm.prototype.formSubmit = function() {
	this.setFormData();
	this.formValidation();

	if(this.error === true){
		return;
	}

	var thisObj = this;
	this.emailFieldSelector = $('#login_email');
	this.passwordFieldSelector = $('#login_password');
	window.location.replace(thisObj.webURL + '/login?email='+ this.emailFieldSelector.val() +'&password='+this.passwordFieldSelector.val());
	
	// var thisObj = this;
	// $.ajax({
	// 	url: thisObj.apiURL + '/user/login',
	// 	method: 'POST',
	// 	data: thisObj.formData,
	// 	beforeSend: function(){

	// 	},
	// 	success: function(resp){
	// 		localStorage.setItem("hot-token", resp.token);
	// 		localStorage.setItem("hot-user", resp.role);
	// 		localStorage.setItem("hot-logged-user", resp.user_id);
	// 		localStorage.setItem("hot-user-full-name", resp.full_name);
	// 		let headers = {
	// 			'authorization': resp.token_type + ' ' + resp.token,
	// 			"content-type": "application/json"
	// 		};
	// 		thisObj.setNextStepLogin(resp.role, headers);
	// 	},
	// 	error: function(response){
	// 		sweetAlertMessage('Error!', response.responseJSON.message, 'error');
	// 	}
	// })
};
loginForm.prototype.setNextStepLogin = function(role, header){
	let thisObj = this;
	switch (role) {
	    case "super-admin":
	      admin = "super_admin";
	      localStorage.setItem("hot-sidebar", admin);
	      $.ajax({
	      	url: thisObj.apiURL + '/company/managerdata',
			method: 'POST',
			data: { email: thisObj.formData.email},
			headers: header,
			success: function(resp){
				localStorage.setItem("hot-user-id", resp[0].id);
				window.location.replace(thisObj.webURL + '/dashboard');
			},
			error: function(response){
				sweetAlertMessage('Error!', response.responseJSON.message, 'error');
			}
	      });
	      break;
	    case "company-admin":
	      admin = "admin";
	      localStorage.setItem("hot-sidebar", admin);
	      $.ajax({
	      	url: thisObj.apiURL + '/company/data',
			method: 'POST',
			data: { email: thisObj.formData.email},
			headers: header,
			success: function(resp){
				if (resp.level) {
					localStorage.setItem("hot-company-level", "parent");
				} else {
					localStorage.setItem("hot-company-level", "child");
				}
				localStorage.setItem("hot-admin-id", resp.admin_id);
				localStorage.setItem("hot-user-id", resp[0].id);
				localStorage.setItem("hot-company-name", resp[0].name);
				window.location.replace(thisObj.webURL + '/dashboard');
			},
			error: function(response){
				sweetAlertMessage('Error!', response.responseJSON.message, 'error');
			}
	      });
	      break;
	    case "manager":
	      admin = "manager";
	      localStorage.setItem("hot-sidebar", admin);
	      $.ajax({
	      	url: thisObj.apiURL + '/company/managerdata',
			method: 'POST',
			data: { email: thisObj.formData.email},
			headers: header,
			success: function(resp){
				localStorage.setItem("hot-user-id", resp[0].id);
				localStorage.setItem("hot-user-name", resp[0].full_name);
				localStorage.setItem("hot-user-number", resp[0].phone_num);
				localStorage.setItem("hot-user-2fa", resp[0].is_2f_authenticated);
				window.location.replace(thisObj.webURL + '/dashboard');
			},
			error: function(response){
				sweetAlertMessage('Error!', response.responseJSON.message, 'error');
			}
	      });
	      break;
	    case "employee":
	      admin = "employee";
	      localStorage.setItem("hot-sidebar", admin);
	      $.ajax({
	      	url: thisObj.apiURL + '/employees/user_data',
			method: 'POST',
			data: { user_name: thisObj.formData.email},
			headers: header,
			success: function(resp){
				localStorage.setItem("hot-user-id", resp[0].id);
				localStorage.setItem("hot-user-name", resp[0].full_name);
				localStorage.setItem("hot-user-number", resp[0].phone_num);
				localStorage.setItem("hot-user-2fa", resp[0].is_2f_authenticated);
				if (resp[0].employee_status == 0) {
					sweetAlertMessage('Error!', 'Account is Deactivated by Admin..!!', 'error');
				} else {
					window.location.replace(thisObj.webURL + '/dashboard');
				}
			},
			error: function(response){
				sweetAlertMessage('Error!', response.responseJSON.message, 'error');
			}
	      });
	      break;
	    default:
	    	sweetAlertMessage('Error!', 'Not Valid..!!', 'error');
	  }
}
loginForm.prototype.setFormData = function(){
	this.formData = {
		'email': this.emailFieldSelector.val(), 
		'password': this.passwordFieldSelector.val()
	}
}

function sweetAlertMessage(title, text, icon){
	swal({
	  title: title,
	  text: text,
	  icon: icon,
	  button: "Ok",
	});
}
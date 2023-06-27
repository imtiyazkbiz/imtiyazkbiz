$(document).ready(function(){
	var contactObj = new contactForm();

	$(document).on('click', '.submit-btn', function(){
		contactObj.formSubmit();
	});
});

function contactForm(){
	this.nameField = $('#name');
	this.emailField = $('#email');
	this.subjectField = $('#subject');
	this.messageField = $('#message-box');
	this.submitButton = $('#submit-btn');
	this.formId = $('#contact-form');
	this.formData = {};
}

contactForm.prototype.formValidation = function() {
	this.error = false;
	if(this.formData.name.length === 0){
		this.nameField.css("border-color", "red");
		this.error = true;
	}else{
		this.nameField.css("border-color", "");
	}
	if(this.formData.email.length === 0 || /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(this.formData.email) === false){
		this.emailField.css("border-color", "red");
		this.error = true;
	}else{
		this.emailField.css("border-color", "");
	}
	if(this.formData.subject.length === 0){
		this.subjectField.css("border-color", "red");
		this.error = true;
	}else{
		this.subjectField.css("border-color", "");
	}
	if(this.formData.message.length === 0){
		this.messageField.css("border-color", "red");
		this.error = true;
	}else{
		this.messageField.css("border-color", "");
	}
};

contactForm.prototype.formSubmit = function() {
	this.setFormData();
	this.formValidation();

	if(this.error === true){
		return;
	}
	var thisObj = this;
	$.ajax({
		url: 'https://api.homeoftraining.com/front-end/contact-us',
		method: 'POST',
		data: thisObj.formData,
		beforeSend: function(){
			thisObj.submitButton.prop('disabled', true);
			thisObj.submitButton.text('Processing....');
		},
		success: function(response){
			thisObj.formId[0].reset();
			alert("Message sent");
		}
	})
};

contactForm.prototype.setFormData = function(){
	this.formData = {
		'name': this.nameField.val(), 
		'email': this.emailField.val(), 
		'subject': this.subjectField.val(), 
		'message': this.messageField.val(), 
	}
}
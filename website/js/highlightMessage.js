$('html, body').animate(
	{ scrollTop: $('#windowDefaultTop').offset().top - document.getElementsByClassName('navbar-fixed-top')[0].clientHeight },
	700
);
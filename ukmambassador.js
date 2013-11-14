jQuery(document).on('click', '.details_hide', function(){
 var amb = jQuery(this).parents('li');
 jQuery(this).hide();
 amb.find('.actions .details_show').show();
 amb.find('.details').slideUp();
});

jQuery(document).on('click', '.details_show', function(){
 var amb = jQuery(this).parents('li');
 jQuery(this).hide();
 amb.find('.actions .details_hide').show();
 amb.find('.details').slideDown();
});

jQuery(document).on('click', '.onface', function(){
	window.open(jQuery(this).attr('data-url'),'_blank');
});


jQuery(document).on('click', '.delete', function(){
	alert('Kommer snart!');
});



jQuery(document).on('click', '#closeInvite', function(){
 jQuery('#buttonInvite').show();
 jQuery('#formInvite').slideUp();
});

jQuery(document).on('click', '#buttonInvite', function() {
  jQuery('#formInvite').slideDown();
  jQuery(this).hide();
});
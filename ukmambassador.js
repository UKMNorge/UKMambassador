jQuery(document).ready(function(){
	var url = window.location.href.split('?')[0] + '?page=UKMSMS_gui';
	var form = jQuery('<form action="' + url + '" method="post" id="AMBinvite_form">' +
						 '<input type="hidden" name="UKMSMS_message" id="AMBinvite_message" value="" />' +
					  '</form>');
	jQuery('body').append(form);
});


jQuery(document).on('click','.toggleMore', function(){
    jQuery(this).parents('tr.ambassador').find('.xs-infos').toggleClass('hidden-xs', 400);
});


jQuery(document).on('click', '#buttonInvite', function(){
    var message = 'Vi vil at du skal bli UKM-ambassadør! Gå inn på https://ambassador.ukm.no for å registrere deg';
    jQuery('#AMBinvite_message').val( message );
	jQuery('#AMBinvite_form').submit();
});
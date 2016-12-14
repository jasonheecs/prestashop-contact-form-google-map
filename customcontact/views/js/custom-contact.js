var CustomContact = (function($) {
	"use strict";

	function init() {
		var windowHeight = $(window).height();
		var headerHeight = $("#header").outerHeight();
		$("#gmap").css("height", Math.max(windowHeight - headerHeight, 400) + "px");
	}

	return {
		init: init
	};
})(jQuery);

$(function() {
	CustomContact.init();
});
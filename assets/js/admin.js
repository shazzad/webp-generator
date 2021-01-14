(function ($) {
	$(document).ready(
		function () {
			$('#webpgen-start-btn').on(
				'click',
				function () {

					var $btn = $(this);
					$btn.prop('disabled', true).addClass('loading').text(webpgen.textScheduling);

					$('.webpgen-generate-logs').empty();
					$('.webpgen-ajax-message').empty();

					$.post(ajaxurl, { action: 'webpgen_schedule' })
						.done(
							function (r) {
								$('.webpgen-ajax-message').text(r.data.message).show();

								if (r.success) {
									updateLog($btn, 1, 1);
								} else {
									$btn.prop('disabled', false).removeClass('loading').text(webpgen.textNormal);
								}
							}
						);

					return false;
				}
			);

			function updateLog($btn, sequence, lid) {
				var
					data = {
						action: 'webpgen_generate',
						sequence: sequence
					},
					$logs = $('.webpgen-generate-logs');

				$btn.text(webpgen.textGenerating);
				$('.webpgen-widget-logs').show();

				$.post(ajaxurl, data)
					.done(
						function (r) {
							$('.webpgen-ajax-message').text(r.data.message).show();

							if (r.data.logs) {
								$(r.data.logs).each(
									function (line, log) {
										$logs.prepend(lid + '. ' + log + '<br />');
										lid += 1;
									}
								);
							}

							if (r.data.resend) {
								updateLog($btn, sequence + 1, lid);
							} else {

								if (r.success) {
									$btn.removeClass('loading').text(r.data.buttonText);
									setTimeout(
										function () {
											$btn.prop('disabled', false).text(webpgen.textGenerateAgain);
										},
										2000
									);

								} else {
									$btn.prop('disabled', false).removeClass('loading').text(webpgen.textNormal).blur();
								}
							}
						}
					);
			}
		}
	);
})(jQuery);

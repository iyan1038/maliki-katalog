<?php defined('BASEPATH') OR exit('No direct script access allowed');
$ek_form_errors       = isset($form_errors) ? $form_errors : NULL;
$ek_toast_flash_error = isset($toast_flash_error) ? $toast_flash_error : NULL;
$ek_toast_messages    = array();

if ($ek_form_errors)
{
	$ek_toast_messages[] = $ek_form_errors;
}

if ($ek_toast_flash_error)
{
	$ek_toast_messages[] = $ek_toast_flash_error;
}
?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;" id="ekToastContainer"></div>
<script>
(function () {
	'use strict';

	function ektAddToast(html, type) {
		var container = document.getElementById('ekToastContainer');
		if (!container) return;

		var el = document.createElement('div');
		el.className = 'toast align-items-center text-bg-' + (type || 'danger') + ' border-0';
		el.setAttribute('role', 'alert');
		el.setAttribute('aria-live', 'assertive');
		el.setAttribute('aria-atomic', 'true');

		var flex = document.createElement('div');
		flex.className = 'd-flex';

		var body = document.createElement('div');
		body.className = 'toast-body';
		body.innerHTML = html;

		var close = document.createElement('button');
		close.type = 'button';
		close.className = 'btn-close btn-close-white me-2 m-auto';
		close.setAttribute('data-bs-dismiss', 'toast');
		close.setAttribute('aria-label', 'Close');

		flex.appendChild(body);
		flex.appendChild(close);
		el.appendChild(flex);
		container.appendChild(el);

		if (typeof bootstrap !== 'undefined') {
			var toast = new bootstrap.Toast(el, { delay: 6000 });
			toast.show();
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		var messages = <?php echo json_encode($ek_toast_messages); ?>;
		for (var i = 0; i < messages.length; i++) {
			if (messages[i]) ektAddToast(messages[i], 'danger');
		}
	});
})();
</script>
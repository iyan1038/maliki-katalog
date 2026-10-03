// E-Katalog — script aplikasi
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		// Tracking klik tombol marketplace (personalization)
		// URL endpoint dibaca dari atribut data pada elemen script pengaktif.
		var trackUrl = document.querySelector('body').getAttribute('data-track-url');
		var links = document.querySelectorAll('.ek-market-link');

		if (trackUrl && links.length) {
			links.forEach(function (link) {
				link.addEventListener('click', function () {
					var pid = link.getAttribute('data-product-id');
					var plid = link.getAttribute('data-platform-id');
					if (!pid) return;

					var fd = new FormData();
					fd.append('type', 'click');
					fd.append('product_id', pid);
					if (plid) fd.append('platform_id', plid);

					navigator.sendBeacon(trackUrl, fd);
				});
			});
		}

		// Navigasi kategori: panah kiri/kanan untuk scroll baris tunggal.
		var catNav = document.getElementById('ekCatNav');
		if (catNav) {
			var prevBtn = catNav.parentElement.querySelector('.ek-cat-arrow-prev');
			var nextBtn = catNav.parentElement.querySelector('.ek-cat-arrow-next');

			function updateArrows() {
				if (!prevBtn || !nextBtn) return;
				var isScrollable = catNav.scrollWidth > catNav.clientWidth + 1;
				if (!isScrollable) {
					prevBtn.classList.add('disabled');
					nextBtn.classList.add('disabled');
					return;
				}
				if (catNav.scrollLeft <= 2) {
					prevBtn.classList.add('disabled');
				} else {
					prevBtn.classList.remove('disabled');
				}
				if (catNav.scrollLeft + catNav.clientWidth >= catNav.scrollWidth - 2) {
					nextBtn.classList.add('disabled');
				} else {
					nextBtn.classList.remove('disabled');
				}
			}

			if (prevBtn) {
				prevBtn.addEventListener('click', function () {
					catNav.scrollBy({ left: -catNav.clientWidth * 0.8, behavior: 'smooth' });
				});
			}
			if (nextBtn) {
				nextBtn.addEventListener('click', function () {
					catNav.scrollBy({ left: catNav.clientWidth * 0.8, behavior: 'smooth' });
				});
			}

			catNav.addEventListener('scroll', updateArrows);
			window.addEventListener('resize', updateArrows);
			updateArrows();
		}

		// Saran riwayat pencarian: muncul saat input di-fokus, terfilter live
		// saat mengetik. Enter memakai opsi yang sedang aktif; tanpa opsi aktif
		// Enter tetap berlaku seperti submit biasa saat tidak ada opsi aktif.
		var sugInput = document.querySelector('.ek-search input[name="q"]');
		var sugList = document.getElementById('ekSearchSuggest');

		if (sugInput && sugList) {
			var sugItems = Array.prototype.slice.call(sugList.querySelectorAll('.ek-suggest-item'));
			var sugOpen = false;
			var sugIndex = -1;

			function sugVisible() {
				return sugItems.filter(function (item) { return ! item.hidden; });
			}

			function sugRender() {
				var q = sugInput.value.trim().toLowerCase();

				sugItems.forEach(function (item) {
					var kw = item.getAttribute('data-kw').toLowerCase();
					item.hidden = q !== '' && kw.indexOf(q) === -1;
				});
			}

			function sugHighlight(idx) {
				var vis = sugVisible();

				sugItems.forEach(function (item) {
					item.classList.remove('is-active');
					item.setAttribute('aria-selected', 'false');
				});

				if (idx < 0 || idx >= vis.length) {
					sugIndex = -1;
					sugInput.removeAttribute('aria-activedescendant');
					return;
				}

				sugIndex = idx;
				vis[idx].classList.add('is-active');
				vis[idx].setAttribute('aria-selected', 'true');
				sugInput.setAttribute('aria-activedescendant', vis[idx].id);
				vis[idx].scrollIntoView({ block: 'nearest' });
			}

			function sugShow() {
				if (sugOpen) return;
				sugOpen = true;
				sugList.hidden = false;
				sugInput.setAttribute('aria-expanded', 'true');
			}

			function sugHide() {
				if ( ! sugOpen) return;
				sugOpen = false;
				sugList.hidden = true;
				sugHighlight(-1);
			}

			function sugChoose(item) {
				sugInput.value = item.getAttribute('data-kw');
				sugHide();
				sugInput.form.submit();
			}

			sugInput.addEventListener('focus', function () {
				sugRender();
				if (sugVisible().length) sugShow();
			});

			sugInput.addEventListener('input', function () {
				sugRender();
				sugHighlight(-1);
				if (sugVisible().length) sugShow(); else sugHide();
			});

			sugInput.addEventListener('keydown', function (e) {
				var vis = sugVisible();

				if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
					if ( ! vis.length) return;
					e.preventDefault();

					if ( ! sugOpen) {
						sugShow();
						sugHighlight(0);
						return;
					}

					var next = sugIndex + (e.key === 'ArrowDown' ? 1 : -1);

					if (next < 0) next = vis.length - 1;
					if (next >= vis.length) next = 0;
					sugHighlight(next);
					return;
				}

				if (e.key === 'Enter' && sugOpen && sugIndex > -1) {
					e.preventDefault();
					sugChoose(vis[sugIndex]);
					return;
				}

				if (e.key === 'Escape') sugHide();
			});

			// Cegah input kehilangan fokus sebelum click pada opsi mendarat.
			sugList.addEventListener('mousedown', function (e) {
				e.preventDefault();
			});

			sugList.addEventListener('click', function (e) {
				var item = e.target.closest('.ek-suggest-item');
				if (item) sugChoose(item);
			});

			document.addEventListener('click', function (e) {
				if ( ! sugList.contains(e.target) && ! sugInput.contains(e.target)) sugHide();
			});
		}

		// Riwayat "pilih beberapa": pilih semua + penghitung item terpilih.
		//
		// Checkbox TIDAK berada di dalam form hapus-terpilih — ia di luar
		// form itu dan hanya ditautkan lewat atribut form="..." (form tidak
		// boleh bersarang, tiap kartu punya form trash sendiri). Karena itu
		// pencarian checkbox memakai form.elements, bukan querySelector.
		//
		// Server sudah merender checkbox, tombol, dan form-nya, jadi semua
		// tetap berfungsi tanpa JS. Blok ini hanya menambah kenyamanan.
		Array.prototype.forEach.call(document.querySelectorAll('[data-ek-select-all]'), function (toggle) {
			var form = document.getElementById(toggle.getAttribute('data-ek-select-all'));

			if ( ! form) return;

			var boxes = Array.prototype.filter.call(form.elements, function (el) {
				return el.type === 'checkbox' && el.name === 'items[]';
			});

			if ( ! boxes.length) return;

			var countEl  = form.querySelector('[data-ek-bulk-count]');
			var submitEl = form.querySelector('[data-ek-bulk-submit]');

			function checkedCount() {
				var n = 0;
				boxes.forEach(function (box) { if (box.checked) n++; });
				return n;
			}

			function refresh() {
				var n = checkedCount();

				if (countEl) countEl.textContent = ' (' + n + ')';

				toggle.checked = n > 0 && n === boxes.length;
				toggle.indeterminate = n > 0 && n < boxes.length;

				if (submitEl) submitEl.disabled = (n === 0);
			}

			toggle.addEventListener('change', function () {
				boxes.forEach(function (box) { box.checked = toggle.checked; });
				refresh();
			});

			boxes.forEach(function (box) {
				box.addEventListener('change', refresh);
			});

			// Jaga-jaga: submit tanpa item terpilih tidak pernah sampai ke server.
			form.addEventListener('submit', function (e) {
				if ( ! checkedCount()) e.preventDefault();
			});

			refresh();
		});
	});
})();

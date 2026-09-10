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
	});
})();

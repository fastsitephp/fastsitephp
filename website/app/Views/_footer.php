	</main>
	<footer>
		<p>&copy; <?= date('Y') ?> <a href="http://www.conradsollitt.com/" target="_blank">Conrad Sollitt</a></p>
		<p><?= $app->escape($i18n['footer_license']) ?></p>
	</footer>
	<script src="<?= $app->rootDir() ?>js/prism.js" defer></script>
	<script>
		(function() {
			// Mobile Nav Menu
			var openMenu = document.querySelector('.open-menu a');
			var closeMenu = document.querySelector('.close-menu');
			var mobileMenu = document.querySelector('.mobile-menu');
			openMenu.onclick = function(e) { e.preventDefault(); mobileMenu.style.display = ''; };
			closeMenu.onclick = function(e) { e.preventDefault(); mobileMenu.style.display = 'none'; };

			// Handle desktop sub-menus for wide-screen mobile devices (iPad, Android Tablets, etc)
			var subMenus = document.querySelectorAll('.site-nav ul.desktop-nav > li.sub-menu');
			Array.prototype.forEach.call(subMenus, function(subMenu) {
				subMenu.onclick = function() {
					var ul = subMenu.querySelector('ul'); 
					ul.style.display = (ul.style.display === '' ? 'block' : '');
				};
			});
		})();
	</script>
	</body>
</html>

	</main>
	<footer>
		<p>&copy; <?= date('Y') ?> <a href="http://www.conradsollitt.com/" target="_blank">Conrad Sollitt</a></p>
		<p><?= $app->escape($i18n['footer_license']) ?></p>
	</footer>
	<script src="<?= $app->rootDir() ?>js/prism.js" defer></script>
	<script>
		(function() {
			var openMenu = document.querySelector('.open-menu a');
			var closeMenu = document.querySelector('.close-menu');
			var mobileMenu = document.querySelector('.mobile-menu');
			openMenu.onclick = function(e) { e.preventDefault(); mobileMenu.style.display = ''; };
			closeMenu.onclick = function(e) { e.preventDefault(); mobileMenu.style.display = 'none'; };
		})();
	</script>
	</body>
</html>

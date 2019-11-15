<!DOCTYPE html>
<html lang="<?= $app->lang ?>">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		
		<title>FastSitePHP | <?= (isset($page_title) ? $app->escape($page_title) : $app->escape($i18n['page_title'])) ?></title>

		<link rel="shortcut icon" href="<?= $app->rootDir() ?>favicon.ico" type="image/x-icon" />
		<link type="text/plain" rel="author" href="<?= $app->rootDir() ?>humans.txt" />

		<link href="<?= $app->rootDir() ?>js/prism.css" rel="stylesheet" />
		<?= $site_css ?>
	</head>
	<body>
		<nav class="site-nav">
			<div class="mobile-nav">
				<span class="site-title"><a href="<?= $app->rootUrl() . $app->lang ?>/">
					<svg width="16px" height="16px" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
						<g id="Buttons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
							<g id="home" fill="#4F5B93">
								<rect id="Rectangle" x="2" y="10" width="12" height="6"></rect>
								<polygon id="Triangle" points="8 4 14 10 2 10"></polygon>
								<rect id="Rectangle" transform="translate(11.500000, 4.750000) rotate(45.000000) translate(-11.500000, -4.750000) " x="6" y="4" width="11" height="1.5" rx="0.75"></rect>
								<rect id="Rectangle" transform="translate(4.500000, 4.750000) scale(-1, 1) rotate(45.000000) translate(-4.500000, -4.750000) " x="-1" y="4" width="11" height="1.5" rx="0.75"></rect>
								<polygon id="Rectangle" points="12 0 14 0 14 5 12 3"></polygon>
							</g>
						</g>
					</svg>
					<span style="margin-left:8px;">FastSitePHP</span>
				</a></span>
				<span class="open-menu"><a href="#">
					<svg width="16px" height="16px" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
						<g id="Buttons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
							<g id="open-menu" transform="translate(0.000000, -1.000000)" fill="#4F5B93">
								<rect id="Rectangle" x="0" y="2" width="16" height="3" rx="1.5"></rect>
								<rect id="Rectangle" x="0" y="8" width="16" height="3" rx="1.5"></rect>
								<rect id="Rectangle" x="0" y="14" width="16" height="3" rx="1.5"></rect>
							</g>
						</g>
					</svg>
				</a></span>				
			</div>
			<div class="mobile-menu" style="display:none;">
				<div>
					<span class="close-menu"><a href="#">
						<svg width="16px" height="16px" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
							<g id="Buttons" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<g id="close-menu" fill="#4F5B93">
									<rect id="Rectangle" transform="translate(7.707107, 8.207107) rotate(45.000000) translate(-7.707107, -8.207107) " x="-2.29289322" y="6.70710678" width="20" height="3" rx="1.5"></rect>
									<rect id="Rectangle" transform="translate(8.131728, 8.131728) scale(-1, 1) rotate(45.000000) translate(-8.131728, -8.131728) " x="-1.86827202" y="6.63172798" width="20" height="3" rx="1.5"></rect>
								</g>
							</g>
						</svg>
					</a></span>
				</div>
				<ul>
					<li<?= (isset($nav_active_link) && $nav_active_link === 'playground' ? ' class="active"' : '') ?>>
						<a href="<?= $app->rootUrl() . $app->lang ?>/playground"><?= $app->escape($i18n['menu_playground']) ?></a>
					</li>
					<li<?= (isset($nav_active_link) && $nav_active_link === 'getting-started' ? ' class="active"' : '') ?>>
						<a href="<?= $app->rootUrl() . $app->lang ?>/getting-started"><?= $app->escape($i18n['menu_getting_started']) ?></a>
					</li>
					<li<?= (isset($nav_active_link) && $nav_active_link === 'quick-reference' ? ' class="active"' : '') ?>>
						<a href="<?= $app->rootUrl() . $app->lang ?>/quick-reference"><?= $app->escape($i18n['menu_quick_ref']) ?></a>
					</li>
					<li<?= (isset($nav_active_link) && $nav_active_link === 'examples' ? ' class="active"' : '') ?>>
						<a href="<?= $app->rootUrl() . $app->lang ?>/examples"><?= $app->escape($i18n['menu_examples']) ?></a>
					</li>				
					<li<?= (isset($nav_active_link) && $nav_active_link === 'documents' ? ' class="active"' : '') ?>>
						<a href="<?= $app->rootUrl() . $app->lang ?>/documents"><?= $app->escape($i18n['menu_documents']) ?></a>
					</li>
					<li<?= (isset($nav_active_link) && $nav_active_link === 'api' ? ' class="active"' : '') ?>>
						<a href="<?= $app->rootUrl() . $app->lang ?>/api"><?= $app->escape($i18n['menu_api']) ?></a>
					</li>
				</ul>			
			</div>
			<ul class="desktop-nav">
				<li<?= (isset($nav_active_link) && $nav_active_link === 'home' ? ' class="active"' : '') ?>>
					<a href="<?= $app->rootUrl() . $app->lang ?>/"><?= $app->escape($i18n['menu_home']) ?></a>
				</li>
				<li<?= (isset($nav_active_link) && $nav_active_link === 'playground' ? ' class="active"' : '') ?>>
					<a href="<?= $app->rootUrl() . $app->lang ?>/playground"><?= $app->escape($i18n['menu_playground']) ?></a>
				</li>
				<li<?= (isset($nav_active_link) && $nav_active_link === 'getting-started' ? ' class="active"' : '') ?>>
					<a href="<?= $app->rootUrl() . $app->lang ?>/getting-started"><?= $app->escape($i18n['menu_getting_started']) ?></a>
				</li>
				<li<?= (isset($nav_active_link) && $nav_active_link === 'quick-reference' ? ' class="active"' : '') ?>>
					<a href="<?= $app->rootUrl() . $app->lang ?>/quick-reference"><?= $app->escape($i18n['menu_quick_ref']) ?></a>
				</li>
				<li<?= (isset($nav_active_link) && $nav_active_link === 'examples' ? ' class="active"' : '') ?>>
					<a href="<?= $app->rootUrl() . $app->lang ?>/examples"><?= $app->escape($i18n['menu_examples']) ?></a>
				</li>				
				<li<?= (isset($nav_active_link) && $nav_active_link === 'documents' ? ' class="active"' : '') ?>>
					<a href="<?= $app->rootUrl() . $app->lang ?>/documents"><?= $app->escape($i18n['menu_documents']) ?></a>
				</li>
				<li<?= (isset($nav_active_link) && $nav_active_link === 'api' ? ' class="active"' : '') ?>>
					<a href="<?= $app->rootUrl() . $app->lang ?>/api"><?= $app->escape($i18n['menu_api']) ?></a>
				</li>
			</ul>
		</nav>
		<main>

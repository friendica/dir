<?php

if(x($_SESSION['uid'])) {
		$a->page['nav'] .= '<a id="nav-logout-link" class="nav-link" href="logout">' . t('Logout') . "</a>\r\n";
}
else {
		$a->page['nav'] .= '<a id="nav-login-link" class="nav-login-link" href="login">' . t('Login') . "</a>\r\n";
}

	$a->page['nav'] .= "<span id=\"nav-link-wrapper\" >\r\n";


	if(x($_SESSION,'uid')) {

		$a->page['nav'] .= '<a id="nav-network-link" class="nav-commlink" href="network">' . t('Network') 
			. '</a><span id="net-update" class="nav-ajax-left"></span>' . "\r\n";

		$a->page['nav'] .= '<a id="nav-home-link" class="nav-commlink" href="profile/' . $a->user['nickname'] . '">' 
			. t('Home') . '</a><span id="home-update" class="nav-ajax-left"></span>' . "\r\n";

		$a->page['nav'] .= '<a id="nav-notify-link" class="nav-commlink" href="notifications">' . t('Notifications') 
			. '</a><span id="notify-update" class="nav-ajax-left"></span>' . "\r\n";

		$a->page['nav'] .= '<a id="nav-messages-link" class="nav-commlink" href="message">' . t('Messages') 
			. '</a><span id="mail-update" class="nav-ajax-left"></span>' . "\r\n";
		

		$a->page['nav'] .= '<a id="nav-settings-link" class="nav-link" href="settings">' . t('Settings') . "</a>\r\n";

		$a->page['nav'] .= '<a id="nav-profiles-link" class="nav-link" href="profiles">' . t('Profiles') . "</a>\r\n";

		$a->page['nav'] .= '<a id="nav-contacts-link" class="nav-link" href="contacts">' . t('Contacts') . "</a>\r\n";


		
	}

	$a->page['nav'] .= "</span>\r\n<span id=\"nav-end\"></span>\r\n";

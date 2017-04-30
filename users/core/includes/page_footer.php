<?php
/*
UserSpice
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
?>
</div> <!-- Close page content container from Header.php -->

<div class="container">
	<div class="row">
	<hr>
		<div class="col-xs-12 text-center">
			<footer><?=configGet('copyright_message')?> &copy; 2016</footer>
			<?php
			if (configGet('debug_mode')){
				echo "<h2>IN DEBUG MODE</h2>";
				echo "Queries this page: ".$GLOBALS['db']->getQueryCount();
			}

			?>
			<?php if(configGet('recaptcha_public') == "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI") { echo "<h3 align='center'>For security reasons, you need to change your reCAPTCHA key.</h3>"; } ?>
		</div>
	</div>
</div>

<!-- jQuery -->
<script src="<?= US_URL_ROOT."resources/js/jquery.js" ?>"></script>

<!-- Bootstrap Core JavaScript -->
<script src="<?= US_URL_ROOT."resources/js/bootstrap.min.js" ?>"></script>

<!-- SmartMenus 1.0.1 and bootstrap plugin -->
<script src="<?= US_URL_ROOT."resources/js/jquery.smartmenus.js" ?>"></script>
<script src="<?= US_URL_ROOT."resources/js/jquery.smartmenus.bootstrap.js" ?>"></script>

<?php
if (isset($footerSnippets) && $footerSnippets) {
    echo '<!-- Snippets using $footerSnippets -->'."\n";
    foreach ((array)$footerSnippets as $f) {
        echo $f."\n";
    }
}
?>

<?php
/*
Plugin Name: mailto:staff
Plugin URI: http://decaf.de/mailto-staff/
Description: Generates mailto links on the dashboard referring to all user groups of the blog. Quite handy way of internal staff communication on multi-author/team blogs.
Version: 2.4.2
Author: DECAF
Author URI: http://decaf.de


	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
*/


  function mailto_staff_widget() {
  	mailto('widget');
  } 

  function add_mailto_staff() {
    global $wp_version;
    if (current_user_can('subscriber') && $wp_version >= "2.7") {
      // subscribers only: add new widget as activity box widget is not available
      // needs WP widget API implemented with WP 2.7
  	  wp_add_dashboard_widget('mailto_staff_widget', __('contact'), 'mailto_staff_widget');	
  	}
  	else {
      // default behaviour: add to activity box widget
      add_action('activity_box_end', 'mailto');    
  	}
  } 

  add_action('wp_dashboard_setup', 'add_mailto_staff' );




  function mailto($mode) {
		global $wpdb, $current_user, $wp_version;

		/* get user data */

		$admins       = array();
		$editors      = array();
		$authors      = array();
		$contributors = array();
		$subscribers  = array();

		$user_ids = $wpdb->get_col("SELECT ID FROM $wpdb->users");
		foreach($user_ids as $user_id) {

			if ($user_id == $current_user->ID) continue; // skip current user

			$user = get_userdata($user_id);
			$user_level = $user->user_level;
			$user_email = $user->user_email;

			// admins
			if ($user_level >= 8 && $user_level <= 10) {
				$admins[] = $user_email;
			}
			// editors
			if ($user_level >= 5 && $user_level <= 7 && $current_user->user_level >= 1) {
				$editors[] = $user_email;
			}
			// authors
			if ($user_level >= 2 && $user_level <= 4 && $current_user->user_level >= 2) {
				$authors[] = $user_email;
			}
			// contributors
			if ($user_level == 1 && $current_user->user_level >= 5) {
				$contributors[] = $user_email;
			}
			// subscribers
			if ($user_level == 0 && $current_user->user_level >= 5) {
				$subscribers[] = $user_email;
			}
		}

		/* preselection

		   admin:        [X] admins  [X] editors  [X] authors  [X] contributors  [ ] subscribers
		   editor:       [ ] admins  [X] editors  [X] authors  [X] contributors  [ ] subscribers
		   author:       [ ] admins  [X] editors  [X] authors  [ ] contributors  [ ] subscribers
		   contributor:  [ ] admins  [X] editors  [ ] authors  [ ] contributors  [ ] subscribers
		   subscriber:   [X] admins  [ ] editors  [ ] authors  [ ] contributors  [ ] subscribers
		*/

		$preselection = '';
		if ($current_user->user_level >= 8 && $current_user->user_level <= 10) {
			// is admin
			$preselection = '"true", "true", "true", "true", "false"';
		}
		if ($current_user->user_level >= 5 && $current_user->user_level <= 7) {
			// is editor
			$tempadmins = '"false"';
			if (count($editors) < 1) $tempadmins = '"true"'; // no editors? select admins			
			$preselection = $tempadmins.', "true", "true", "true", "false"';
		}
		if ($current_user->user_level >= 2 && $current_user->user_level <= 4) {
			// is author
			$tempadmins = '"false"';
			if (count($editors) < 1) $tempadmins = '"true"'; // no editors? select admins
			$preselection = $tempadmins.', "true", "true", "false", "false"';
		}
		if ($current_user->user_level == 1) {
			// is contributor
			$tempadmins = '"false"';
			if (count($editors) < 1) $tempadmins = '"true"'; // no editors? select admins
			$preselection = $tempadmins.', "true", "false", "false", "false"';
		}
		if ($current_user->user_level == 0) {
			// is subscriber
			$preselection = '"true", "false", "false", "false", "false"';
		}

		/* check i18n */

		if (function_exists('_x')) {
			// wp 2.8+
			$i18n_administrator = _x('Administrator', 'User role');
			$i18n_editor        = _x('Editor', 'User role');
			$i18n_author        = _x('Author', 'User role');
			$i18n_contributor   = _x('Contributor', 'User role');
			$i18n_subscriber    = _x('Subscriber', 'User role');
		}
		else {
			// wp < 2.8
			$i18n_administrator = __('Administrator|User role');
			$i18n_editor        = __('Editor|User role');
			$i18n_author        = __('Author|User role');
			$i18n_contributor   = __('Contributor|User role');
			$i18n_subscriber    = __('Subscriber|User role');
			if (strpos(__('Administrator|User role'), 'User role')) {
				// no i18n -> use english user definitions
				$i18n_administrator = 'Administrator';
				$i18n_editor        = 'Editor';
				$i18n_author        = 'Author';
				$i18n_contributor   = 'Contributor';
				$i18n_subscriber    = 'Subscriber';
			}
		}

		/* build HTML */

		$output     = '';
		$html       = '';
		$vars       = '';
		$numpeople  = count($admins)+count($editors)+count($authors)+count($contributors)+count($subscribers);

		if (count($admins) > 0) {
			$html.= ' <label style=\"margin-right:10px;white-space:nowrap;\"><input style=\"vertical-align: baseline;\" id=\"mailto1\" onclick=\"toggle()\" type=\"checkbox\" name=\"chk1\" value=\"admins\" /> '.$i18n_administrator.' ('.count($admins).')<\/label>';
			$vars.= 'admins = new Array("'.implode("\",\"", $admins).'"); ';
		}
		if (count($editors) > 0) {
			$html.= ' <label style=\"margin-right:10px;white-space:nowrap;\"><input style=\"vertical-align: baseline;\" id=\"mailto2\" onclick=\"toggle()\" type=\"checkbox\" name=\"chk2\" value=\"editors\" /> '.$i18n_editor.' ('.count($editors).')<\/label>';
			$vars.= 'editors = new Array("'.implode("\",\"", $editors).'"); ';
		}
		if (count($authors) > 0) {
			$html.= ' <label style=\"margin-right:10px;white-space:nowrap;\"><input style=\"vertical-align: baseline;\" id=\"mailto3\" onclick=\"toggle()\" type=\"checkbox\" name=\"chk3\" value=\"authors\" /> '.$i18n_author.' ('.count($authors).')<\/label>';
			$vars.= 'authors = new Array("'.implode("\",\"", $authors).'"); ';
		}
		if (count($contributors) > 0) {
			$html.= ' <label style=\"margin-right:10px;white-space:nowrap;\"><input style=\"vertical-align: baseline;\" id=\"mailto4\" onclick=\"toggle()\" type=\"checkbox\" name=\"chk4\" value=\"contributors\" /> '.$i18n_contributor.' ('.count($contributors).')<\/label>';
			$vars.= 'contributors = new Array("'.implode("\",\"", $contributors).'"); ';
		}
		if (count($subscribers) > 0) {
			$html.= ' <label style=\"margin-right:10px;white-space:nowrap;\"><input style=\"vertical-align: baseline;\" id=\"mailto5\" onclick=\"toggle()\" type=\"checkbox\" name=\"chk5\" value=\"subscribers\" /> '.$i18n_subscriber.' ('.count($subscribers).')<\/label>';
			$vars.= 'subscribers = new Array("'.implode("\",\"", $subscribers).'"); ';
		}
		if ($html != '') {

      // wrapper styles
			$divstyle    = 'background:#f9f9f9; border-top:1px solid #ddd; border-bottom:1px solid #ddd; margin: 10px -9px; padding:5px 9px;';
      if ($mode == 'widget') {
        // do not use any wrapper styles if placed in a widget
        unset($divstyle);
      }
			$buttonclass = 'button rbutton';
			// styles for WP < 2.7
			if ($wp_version < "2.7") {
				$divstyle    = 'background:#f9f9f9; border-top:1px solid #ddd; border-bottom:1px solid #ddd; margin: 10px 0; padding:2px 0;';
				$buttonclass = '';
			}
			$html = '<div id=\"mailtostaff\" style=\"'.$divstyle.'\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr><td style=\"white-space:normal;border:0;\"><p style=\"margin:0;\"><span style=\"margin-right:5px;\"><strong><img style=\"vertical-align:middle;\" src=\"/'.PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/'.'group.png\" alt=\"\" /> '.__('E-mail').'<\/strong>: <\/span>'.$html.'<\/p><\/td><td style=\"border:0;\"><a id=\"mailtolink\" style=\"position:relative;top:0;margin-left:10px;\" href=\"#\" class=\"'.$buttonclass.'\"><img style=\"vertical-align:bottom;\" src=\"/'.PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)).'/'.'email_go.png\" alt=\"\" />&nbsp;<strong id=\"mailtocounter\">0<\/strong><\/a><\/td><\/tr><\/table><\/div>';
		}

		/* build output */

		if ($html != '') {
			$output = '

			<script type="text/javascript">

				document.write("'.$html.'");

				var admins = new Array(), editors = new Array(), authors = new Array(), contributors = new Array(), subscribers = new Array();
				'.$vars.'
				var currentuser = "'.$current_user->user_email.'";
				var preselection = new Array("", '.$preselection.');

				function removeselfanddoubles(a) {
					temp = new Array();
					for(i=0;i<a.length;i++) {
						if(!contains(temp, a[i]) && a[i]!=currentuser) {
							temp.length+=1;
							temp[temp.length-1]=a[i];
						}
					}
					return temp;
				}
				function contains(a, e) {
					for(j=0;j<a.length;j++) if(a[j]==e) return true;
					return false;
				}
				function readCookie(name) {
					var nameEQ = name + "=";
					var ca = document.cookie.split(";");
					for(var i=0;i < ca.length;i++) {
						var c = ca[i];
						while (c.charAt(0)==" ") c = c.substring(1,c.length);
						if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
					}
					return null;
				}
				function toggle() {
					var select = new Array();
					for (var x=1; x<=5; x++) {
						select[x] = (document.getElementById("mailto"+x) && document.getElementById("mailto"+x).checked == true) ? "true" : "false";
					}
					document.cookie = "mailtostaff="+select; 

					var mailtolink = "#";
					var recipients = new Array();
					if (admins.length > 0       && select[1] == "true") { recipients = recipients.concat(admins); }
					if (editors.length > 0      && select[2] == "true") { recipients = recipients.concat(editors); }
					if (authors.length > 0      && select[3] == "true") { recipients = recipients.concat(authors); }
					if (contributors.length > 0 && select[4] == "true") { recipients = recipients.concat(contributors); }
					if (subscribers.length > 0  && select[5] == "true") { recipients = recipients.concat(subscribers); }
					recipients = removeselfanddoubles(recipients);

					if (recipients.length > 0) {
						if (document.getElementById("mailto5") && document.getElementById("mailto5").checked == true) {
							mailtolink = "mailto:" + currentuser + "?bcc=" + recipients.join(",");
						}
						else {
							mailtolink = "mailto:" + recipients.join(",");
						}
						document.getElementById("mailtolink").style.visibility = "visible";
					}
					else {
						document.getElementById("mailtolink").style.visibility = "hidden";
					}
					document.getElementById("mailtolink").href = mailtolink;
					document.getElementById("mailtocounter").innerHTML = recipients.length;
				}

				var select = readCookie("mailtostaff");
				select = (select) ? select.split(",") : preselection;
				for (var x=1; x<=5; x++) {
					if (document.getElementById("mailto" + x) && select[x] == "true") document.getElementById("mailto" + x).checked = true;
				}

				window.setTimeout("toggle()", 100);

			</script>';
		}

		/* output */

		echo $output;
	}
?>
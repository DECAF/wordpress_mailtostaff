<?php
/*
Plugin Name: mailto:staff
Plugin URI: http://decaf.de/mailto-staff/
Description: Generates mailto link on the dashboard referring to all user groups of the blog. Quite handy way of internal staff communication on multi-author/team blogs.
Version: 3.1
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


add_action('wp_dashboard_setup', 'add_mailtostaff_widget');

function add_mailtostaff_widget()
{
    add_action('admin_head', 'add_mailtostaff_resources');
    wp_add_dashboard_widget('mailtostaff_widget', 'mailto:staff', 'mailtostaff');
}


/**
 * gets default role for given user.
 * default roles are 'administrator', 'editor', 'author', 'contributor' and 'subscriber'.
 */
function get_default_role_for_user($user = null)
{

    $roles = array('administrator', 'editor', 'author', 'contributor');

    foreach ($roles as $role) {
        if ($user) {
            // use given user
            if (user_can($user, $role)) {
                return $role;
            }
        } else {
            // use current user
            if (current_user_can($role)) {
                return $role;
            }
        }
    }
    return 'subscriber'; // fallback
}


/**
 * gets display name of role
 */
function get_display_name_of_role($role)
{
    $roles = get_option('wp_user_roles');
    $displayName = $roles[$role]['name'];
    if (!empty($displayName)) {
        return sanitize_text_field($displayName); // sanatize. you never know.
    }
    return $role; // fallback
}


/**
 * mailtostaff
 */
function mailtostaff($mode)
{

    // get WP users
    $args = array(
        'exclude' => array(get_current_user_id()), // exclude current user
        'orderby' => 'email',
        'order' => 'ASC'
    );
    $users = get_users($args); // since WP 3.1.0


    // set up an email list array with WP users
    // structure: $emailList[$defaultRole][$role][$email];
    $emailList = array();
    foreach ($users as $user) {
        $user = new WP_User($user->ID); // don't use get_user_by() due to compatibility reasons
        $defaultRole = $role = $email = false; // init
        $defaultRole = get_default_role_for_user($user); // default role
        $email = $user->user_email; // email
        foreach ($user->roles as $role) {
            $emailList[$defaultRole][$role][] = $email; // add
        }
    }


    // filter userlist based on current user's (default) role
    // subscribers can address admins only. contributors can address editors and admins.
    // authors can address authors, editors and admins. editors and admins can address all users
    // hint: custom roles have been merged with default roles based on typical capabilities
    $currentUserRole = get_default_role_for_user();
    if ($currentUserRole == 'subscriber') {
        unset ($emailList['subscriber'], $emailList['contributor'], $emailList['author'], $emailList['editor']);
    }
    if ($currentUserRole == 'contributor') {
        unset ($emailList['subscriber'], $emailList['contributor'], $emailList['author']);
    }
    if ($currentUserRole == 'author') {
        unset ($emailList['subscriber'], $emailList['contributor']);
    }


    // get all roles, skip empty
    $allRoles = array();
    foreach ($emailList as $v1) {
        foreach ($v1 as $k2 => $v2) {
            if (!in_array($k2, $allRoles) && count($v2) > 0) {
                $allRoles[] = $k2;
            }
        }
    }


    // sort all roles: default roles first, custom roles afterwards (A-Z)
    asort($allRoles); // sort A-Z
    $sort = array('subscriber', 'contributor', 'author', 'editor', 'administrator'); // reverse WP roles
    foreach ($sort as $v) {
        $k = array_search($v, $allRoles);
        if ($k !== false) {
            unset ($allRoles[$k]); // remove from array
            array_unshift($allRoles, $v); // prepend saved to the beginning
        }
    }


    // setup mailto list and mailto link (used for non-JS environment only)
    global $current_user;
    $mailtoList = array();
    foreach ($emailList as $v1) {
        foreach ($v1 as $v2) {
            foreach ($v2 as $v3) {
                $mailtoList[] = sanitize_email($v3);
            }
        }
    }
    $mailtoList = array_unique($mailtoList); // remove duplicate values
    $mailtoLink = 'mailto:' . $current_user->user_email . '?bcc=' . implode(",", $mailtoList); // mailto link


    // build selects for each custom role
    $htmlSelects = '';
    foreach ($allRoles as $customRole) {
        $tempEmails = array();
        $tempRoles = array();
        foreach ($emailList as $role => $roleItems) {
            if (is_array($roleItems) && count($roleItems) > 0) {
                foreach ($roleItems as $k => $v) {
                    if ($k === $customRole && is_array($roleItems) && count($roleItems) > 0) {
                        $tempRoles[] = $role;
                        foreach ($v as $email) {
                            $tempEmails[] = $email;
                        }
                    }
                }
            }
        }
        if (count($tempEmails) > 0) {
            $htmlSelects .= PHP_EOL . '
					<label class="mailtostaff__select-item">
						<input type="checkbox" class="mailtostaff__select-input" checked="checked" data-mailtostaff-custom-role="' . esc_attr($customRole) . '" data-mailtostaff-wp-roles="' . esc_attr(implode(",", $tempRoles)) . '" data-mailtostaff-emails="' . esc_attr(implode(",", $tempEmails)) . '">
						<span class="mailtostaff__select-title">' . _x(get_display_name_of_role($customRole), 'User role') . '</span>
						<span class="mailtostaff__select-count">(' . count($tempEmails) . ')</span>
					</label>' . PHP_EOL;
        }
    }

    // build html
    $html = PHP_EOL . '
				<div class="mailtostaff" id="mailtostaff" data-mailtostaff-current-user-address="' . $current_user->user_email . '">
					<div class="mailtostaff__wrapper">
						<div class="mailtostaff__cell mailtostaff__brand">
							<i class="mailtostaff__icon mailtostaff__icon-users"></i>
						</div>
						<div class="mailtostaff__cell mailtostaff__selects hide-if-no-js">
							' . $htmlSelects . '
						</div>
						<div class="mailtostaff__cell mailtostaff__action">
							<a class="button mailtostaff__mailto-link" href="' . $mailtoLink . '"><i class="mailtostaff__icon mailtostaff__icon-mail"></i> <span class="mailtostaff__item-count">' . count($mailtoList) . '</span></a>
						</div>
					</div>
				</div>' . PHP_EOL;

    // output
    echo $html;
}


/**
 * mailtostaff resources
 */
function add_mailtostaff_resources()
{

    // CSS
    ?>
    <style>
        @font-face {
            font-family: "mailtostaff";
            src: url("<?php echo plugins_url('fonts/mailtostaff.eot',             __FILE__ ); ?>");
            src: url("<?php echo plugins_url('fonts/mailtostaff.eot?#iefix',      __FILE__ ); ?>") format("embedded-opentype"),
            url("<?php echo plugins_url('fonts/mailtostaff.woff',            __FILE__ ); ?>") format("woff"),
            url("<?php echo plugins_url('fonts/mailtostaff.ttf',             __FILE__ ); ?>") format("truetype"),
            url("<?php echo plugins_url('fonts/mailtostaff.svg#mailtostaff', __FILE__ ); ?>") format("svg");
            font-weight: normal;
            font-style: normal;
        }

        .mailtostaff__icon {
            font-family: "mailtostaff";
            speak: none;
            font-style: normal;
            font-weight: normal;
            font-variant: normal;
            text-transform: none;
            line-height: 1;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .mailtostaff__icon-mail:before {
            content: "\e604";
        }

        .mailtostaff__icon-users:before {
            content: "\e60e";
        }

        .mailtostaff__icon {
            font-size: 21px;
            display: inline-block;
            vertical-align: middle;
        }

        .mailtostaff__wrapper {
            display: table;
        }

        .mailtostaff__cell {
            display: table-cell;
            vertical-align: top;
        }

        .mailtostaff__brand {
            padding: 2px 10px 0 0;
        }

        .mailtostaff__selects {
            padding: 3px 5px 0 0;
        }

        .mailtostaff__select-item {
            white-space: nowrap;
            margin-right: 13px;
        }

        .mailtostaff__select-count {
            padding-left: 2px;
            color: #888;
            font-size: 0.9em;
        }

        .mailtostaff__action .mailtostaff__icon {
            position: relative;
            top: -1px;
        }
    </style>
    <?php

    // CSS for WP < 3.8
    if (get_bloginfo('version') < 3.8) { ?>
        <style>
            #mailtostaff .mailtostaff__select-input {
                vertical-align: baseline;
            }
        </style>
    <?php }


    // JS
    ?>
    <script>
        jQuery(function ($) {

            var
                mailtoStaff = $('#mailtostaff'),
                currentUserMail = mailtoStaff.data('mailtostaff-current-user-address'),
                mailtoLink = $('.mailtostaff__mailto-link', mailtoStaff),
                mailtoLinkValue,
                mailtoLinkCount = $('.mailtostaff__item-count', mailtoLink),
                emailList = [],
                selectedItems,
                selection = []
                ;

            function getCookie(key) {
                var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
                return keyValue ? keyValue[2] : null;
            }

            function removeDuplicates(array) {
                return $.grep(array, function (el, index) {
                    return index === $.inArray(el, array);
                });
            }

            function updateMailtoLink(mode) {

                // update selection from cookie
                if (mode === 'init') {
                    var cookie = getCookie('wordpress_mailtostaff');
                    if (cookie && typeof cookie === 'string') {
                        cookie = cookie.split(',');
                        mailtoStaff
                            .find('.mailtostaff__select-input')
                            .prop('checked', false)
                            .filter(function (i) {
                                return $.inArray($(this).data('mailtostaff-custom-role'), cookie) >= 0;
                            })
                            .prop('checked', true);
                    }
                }

                // get selected items, generate email list
                emailList = [];
                selectedItems = mailtoStaff.find('.mailtostaff__select-input:checked');
                selectedItems.each(function (i) {
                    var data = ($(this).data('mailtostaff-emails')).split(',');
                    $.each(data, function (i, v) {
                        emailList.push(v);
                    });
                });

                // get custom roles of selected items
                // check for subscribers in wp roles
                selection = [];
                hasSubscribers = false;
                $.each(selectedItems, function (i, v) {
                    selection.push($(v).data('mailtostaff-custom-role'));
                    if ($.inArray('subscriber', ($(v).data('mailtostaff-wp-roles')).split(',')) >= 0) {
                        hasSubscribers = true;
                    }
                });

                // remove duplicate emails
                emailList = removeDuplicates(emailList);

                // set up mailto link
                mailtoLinkValue = '#';
                if (emailList.length > 0) {
                    if (hasSubscribers) {
                        // use BCC for privacy reasons if email list contains subscribers
                        mailtoLinkValue = 'mailto:' + currentUserMail + '?bcc=' + emailList;
                    }
                    else {
                        mailtoLinkValue = 'mailto:' + emailList;
                    }
                }
                mailtoLink.attr('href', mailtoLinkValue);

                // count emails, set mailto link state
                mailtoLinkCount.html(emailList.length);
                if (emailList.length < 1) {
                    mailtoLink.addClass('button-disabled');
                }
                else {
                    mailtoLink.removeClass('button-disabled');
                }

                // save user selection to cookie
                if (mode !== 'init') {
                    var expires = new Date();
                    expires.setTime(expires.getTime() + (365 * 24 * 60 * 60 * 1000));
                    document.cookie = 'wordpress_mailtostaff=' + selection.join(',') + ';expires=' + expires.toUTCString();
                }
            }

            updateMailtoLink('init'); // init
            mailtoStaff.delegate('.mailtostaff__select-input', 'change', updateMailtoLink); // don't use .on() due to compatibility reasons
        });
    </script>
    <?php
}

?>
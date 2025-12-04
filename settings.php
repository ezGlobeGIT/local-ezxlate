<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * @package    local_ezxlate
 * @copyright  2025 EzGlobe
 * @author     Christophe Blanchot <cblanchot@cbcd.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    
    $settings = new admin_settingpage( 'local_ezxlate', get_string('config:title', 'local_ezxlate') );
    
    // Prepare a random key if key is '*'
    if (get_config("local_ezxlate", "key") == '*') {
        $secret = "";
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMONPQRSTUVWXYZ';
        $lim = strlen($chars) -1;
        for($n=1; $n<=50; $n++) {
            $secret .= $chars[mt_rand(0,$lim )];
        }
        set_config("key", $secret, "local_ezxlate");
    }
    
    // Open API : yes / no
    $settings->add(new admin_setting_configselect(
        'local_ezxlate/open',
        get_string('config:open:label', 'local_ezxlate'),
        get_string('config:open:desc', 'local_ezxlate'),
        0,
        [
            1 => get_string('yes'),
            0 => get_string('no')
        ]
    ));
    
    // secret Key
    $settings->add(new admin_setting_configtext(
        'local_ezxlate/key',
        get_string('config:key:label', 'local_ezxlate'),
        get_string('config:key:desc', 'local_ezxlate'),
        ""));
    
    // Allowed IPs
    $settings->add(new admin_setting_configiplist(
        'local_ezxlate/ips',
        get_string('config:ips:label', 'local_ezxlate'),
        get_string('config:ips:desc', 'local_ezxlate'),
        ""));
    
    // User (updates are made by this user, using this user capabilities)
    $users = get_users_by_capability(context_system::instance(), 'local/ezxlate:use');
    $options = [];
    foreach ($users as $u) {
        $options[$u->id] = fullname($u) . ' (' . $u->email . ')';
    }
    asort($options);
    if (empty($options)) $options[0] = get_string('config:user:none', 'local_ezxlate');
    
    $settings->add(new admin_setting_configselect(
        'local_ezxlate/userid',
        get_string('config:user:label', 'local_ezxlate'),
        get_string('config:user:desc', 'local_ezxlate'),
        0,
        $options
    ));
    
    // Verification of previous value required : yes / no
    $settings->add(new admin_setting_configselect(
        'local_ezxlate/previous',
        get_string('config:previous:label', 'local_ezxlate'),
        get_string('config:previous:desc', 'local_ezxlate'),
        0,
        [
            1 => get_string('yes'),
            0 => get_string('no')
        ]
    ));
    
    // Can extend fields (yes/no)
    $settings->add(new admin_setting_configselect(
        'local_ezxlate/extend',
        get_string('config:extend:label', 'local_ezxlate'),
        get_string('config:extend:desc', 'local_ezxlate'),
        0,
        [
            1 => get_string('yes'),
            0 => get_string('no')
        ]
    ));
    
    // gradebook''s names automatic update
    $settings->add(new admin_setting_configselect(
        'local_ezxlate/gradebook',
        get_string('config:gradebook:label', 'local_ezxlate'),
        get_string('config:gradebook:desc', 'local_ezxlate'),
        0,
        [
            1 => get_string('yes'),
            0 => get_string('no')
        ]
    ));
    
    // Context level for questions
    $options = [
        999 => get_string('contextlevel_none', 'local_ezxlate'),
        CONTEXT_COURSE => get_string('contextlevel_course', 'local_ezxlate'),
        CONTEXT_COURSECAT => get_string('contextlevel_coursecat', 'local_ezxlate'),
        CONTEXT_SYSTEM => get_string('contextlevel_system', 'local_ezxlate'),
    ];
    $settings->add(new admin_setting_configselect(
        'local_ezxlate/questions',
        get_string('config:questions:label', 'local_ezxlate'),
        get_string('config:questions:desc', 'local_ezxlate'),
        0,
        $options
    ));
    
    // Allow Export et import of tags 
    $settings->add(new admin_setting_configselect(
        'local_ezxlate/tags',
        get_string('config:tags:label', 'local_ezxlate'),
        get_string('config:tags:desc', 'local_ezxlate'),
        0,
        [
            1 => get_string('yes'),
            0 => get_string('no')
        ]
    ));
    
    // Allowed courses
    $settings->add(new admin_setting_configtextarea(
        'local_ezxlate/allowed_courses',
        get_string('config:allowed_courses:label', 'local_ezxlate'),
        get_string('config:allowed_courses:desc', 'local_ezxlate'),
        ""
            ));
    
    // Restricted courses
    $settings->add(new admin_setting_configtextarea(
        'local_ezxlate/restricted_courses',
        get_string('config:restricted_courses:label', 'local_ezxlate'),
        get_string('config:restricted_courses:desc', 'local_ezxlate'),
        ""));
    $ADMIN->add( 'localplugins', $settings );
}
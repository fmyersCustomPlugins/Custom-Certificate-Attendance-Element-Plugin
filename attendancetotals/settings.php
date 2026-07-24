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
 * Admin settings for customcertelement_attendancetotals.
 *
 * $settings is provided by Moodle's plugin settings loader before this
 * file is included - no need to create it ourselves.
 *
 * These settings apply site-wide, to every "Attendance totals" element on
 * every certificate.
 *
 * @package   customcertelement_attendancetotals
 * @copyright 2026 Finley Myers <finleymwork@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading(
        'customcertelement_attendancetotals/semesterheading',
        get_string('semesterheading', 'customcertelement_attendancetotals'),
        get_string('semesterheading_desc', 'customcertelement_attendancetotals')
    ));

    $settings->add(new admin_setting_configtext(
        'customcertelement_attendancetotals/sem1start',
        get_string('sem1start', 'customcertelement_attendancetotals'),
        get_string('semdate_desc', 'customcertelement_attendancetotals'),
        '',
        PARAM_TEXT,
        12
    ));

    $settings->add(new admin_setting_configtext(
        'customcertelement_attendancetotals/sem1end',
        get_string('sem1end', 'customcertelement_attendancetotals'),
        get_string('semdate_desc', 'customcertelement_attendancetotals'),
        '',
        PARAM_TEXT,
        12
    ));

    $settings->add(new admin_setting_configtext(
        'customcertelement_attendancetotals/sem2start',
        get_string('sem2start', 'customcertelement_attendancetotals'),
        get_string('semdate_desc', 'customcertelement_attendancetotals'),
        '',
        PARAM_TEXT,
        12
    ));

    $settings->add(new admin_setting_configtext(
        'customcertelement_attendancetotals/sem2end',
        get_string('sem2end', 'customcertelement_attendancetotals'),
        get_string('semdate_desc', 'customcertelement_attendancetotals'),
        '',
        PARAM_TEXT,
        12
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'customcertelement_attendancetotals/headingtextcolor',
        get_string('setting_headingtextcolor', 'customcertelement_attendancetotals'),
        get_string('setting_headingtextcolor_desc', 'customcertelement_attendancetotals'),
        '#000000'
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'customcertelement_attendancetotals/headingbgcolor',
        get_string('setting_headingbgcolor', 'customcertelement_attendancetotals'),
        get_string('setting_headingbgcolor_desc', 'customcertelement_attendancetotals'),
        '#f2f2f2'
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'customcertelement_attendancetotals/bodytextcolor',
        get_string('setting_bodytextcolor', 'customcertelement_attendancetotals'),
        get_string('setting_bodytextcolor_desc', 'customcertelement_attendancetotals'),
        '#000000'
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'customcertelement_attendancetotals/bordercolor',
        get_string('setting_bordercolor', 'customcertelement_attendancetotals'),
        get_string('setting_bordercolor_desc', 'customcertelement_attendancetotals'),
        '#999999'
    ));

    $settings->add(new admin_setting_configtext(
        'customcertelement_attendancetotals/labelcolumnwidth',
        get_string('setting_labelcolumnwidth', 'customcertelement_attendancetotals'),
        get_string('setting_labelcolumnwidth_desc', 'customcertelement_attendancetotals'),
        25,
        PARAM_INT
    ));
}

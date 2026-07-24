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
 * Language strings.
 *
 * @package   customcertelement_attendancetotals
 * @copyright 2026 Finley Myers <finleymwork@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Attendance totals';

$string['rowsheading'] = 'Rows to display';
$string['showsem1'] = 'Show Semester 1';
$string['showsem2'] = 'Show Semester 2';
$string['showtotalrow'] = 'Show Total';

$string['columnsheading'] = 'Columns to display';
$string['showpresent'] = 'Show Present';
$string['showlate'] = 'Show Late';
$string['showunexcusedabsent'] = 'Show Unexcused Absent';
$string['showexcusedabsent'] = 'Show Excused Absent';
$string['showtotalcolumn'] = 'Show Total';

$string['semester1'] = 'Semester 1';
$string['semester2'] = 'Semester 2';
$string['totalattendance'] = 'Total';
$string['colpresent'] = 'P';
$string['collate'] = 'L';
$string['colunexcusedabsent'] = 'UA';
$string['colexcusedabsent'] = 'EA';
$string['coltotal'] = 'Total';

$string['modattendancemissing'] = 'The Attendance activity module (mod_attendance) is not installed on this site, so attendance totals cannot be displayed.';

$string['semesterheading'] = 'Semester dates';
$string['semesterheading_desc'] = 'If the block_attendance_summary block is installed and its semester dates are set, this element uses those automatically - you only need to set dates in one place. The fields below are only used as a fallback for any date left blank on the block\'s settings page (or if that block isn\'t installed at all).';
$string['sem1start'] = 'Semester 1 start date (fallback)';
$string['sem1end'] = 'Semester 1 end date (fallback)';
$string['sem2start'] = 'Semester 2 start date (fallback)';
$string['sem2end'] = 'Semester 2 end date (fallback)';
$string['semdate_desc'] = 'Enter the date in YYYY-MM-DD format, e.g. 2026-08-10. Only used if block_attendance_summary doesn\'t provide this date (see the heading above).';

$string['setting_headingtextcolor'] = 'Heading text colour';
$string['setting_headingtextcolor_desc'] = 'Colour of the column headings (P, L, UA, EA, Total) and row labels (Semester 1, Semester 2, Total).';

$string['setting_headingbgcolor'] = 'Heading background colour';
$string['setting_headingbgcolor_desc'] = 'Background colour behind the column headings row.';

$string['setting_bodytextcolor'] = 'Value text colour';
$string['setting_bodytextcolor_desc'] = 'Colour of the attendance count values themselves.';

$string['setting_bordercolor'] = 'Border colour';
$string['setting_bordercolor_desc'] = 'Colour of the lines around each cell in the table.';

$string['setting_labelcolumnwidth'] = 'Row label column width (%)';
$string['setting_labelcolumnwidth_desc'] = 'How much of the table\'s width the row-label column (Semester 1 / Semester 2 / Total) takes up, as a percentage (10-90). The remaining width is split evenly between the visible P/L/UA/EA/Total columns.';

$string['privacy:metadata'] = 'The Attendance totals certificate element only stores column/row display preferences and site-wide colour settings. It does not store any personal data of its own - the attendance data it displays at render time belongs to mod_attendance, which already declares that data to the privacy API.';

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
 * element.php
 *
 * A customcert element that shows a student's Semester 1 / Semester 2 /
 * Total attendance totals (Present / Late / Unexcused Absent / Excused
 * Absent / Total), report-card style, on a certificate.
 *
 * Design: unlike the Quarterly totals grade element, this element is NOT
 * per-course - it mirrors block_attendance_summary, which sums attendance
 * across every mod_attendance activity in every course the student is
 * enrolled in. So you only ever need ONE "Attendance totals" element per
 * certificate, not one per course.
 *
 * This plugin is fully self-contained - it does NOT depend on
 * block_attendance_summary. The attendance-fetching logic below mirrors
 * what that block does (same P/L/UA/EA acronym matching, same semester
 * bucketing), but keeps its own copy and its own semester-date settings
 * so this element can be installed/updated independently.
 *
 * @package   customcertelement_attendancetotals
 * @copyright 2026 Finley Myers <finleymwork@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customcertelement_attendancetotals;

defined('MOODLE_INTERNAL') || die();

class element extends \mod_customcert\element {

    /**
     * Status acronyms (as configured on each mod_attendance status set,
     * e.g. "P", "L", "UA", "EA") mapped to the bucket key we count them
     * under. Matching is case-insensitive and trims whitespace.
     */
    const ACRONYM_MAP = [
        'P'  => 'p',
        'L'  => 'l',
        'UA' => 'ua',
        'EA' => 'ea',
    ];

    /**
     * Single source of truth for every "row" this element can show:
     * the settings-form checkbox name, and the row-label string key.
     *
     * @return array row key ('sem1'|'sem2'|'total') => [checkbox field, label key]
     */
    private static function get_row_defs() {
        return [
            'sem1'  => ['showsem1', 'semester1'],
            'sem2'  => ['showsem2', 'semester2'],
            'total' => ['showtotalrow', 'totalattendance'],
        ];
    }

    /**
     * Single source of truth for every "column" this element can show:
     * the settings-form checkbox name, and the short column-heading
     * string key.
     *
     * @return array bucket key ('p'|'l'|'ua'|'ea'|'total') => [checkbox field, heading key]
     */
    private static function get_column_defs() {
        return [
            'p'     => ['showpresent', 'colpresent'],
            'l'     => ['showlate', 'collate'],
            'ua'    => ['showunexcusedabsent', 'colunexcusedabsent'],
            'ea'    => ['showexcusedabsent', 'colexcusedabsent'],
            'total' => ['showtotalcolumn', 'coltotal'],
        ];
    }

    /**
     * All checkbox field names this element's form uses (rows + columns
     * combined), for save_unique_data() / definition_after_data().
     *
     * @return array field name list
     */
    private static function get_all_checkbox_fields() {
        $fields = [];
        foreach (self::get_row_defs() as $def) {
            $fields[] = $def[0];
        }
        foreach (self::get_column_defs() as $def) {
            $fields[] = $def[0];
        }
        return $fields;
    }

    /**
     * This function renders the form elements when adding a customcert element.
     *
     * @param \MoodleQuickForm $mform the edit_form instance
     */
    public function render_form_elements($mform) {
        $mform->addElement(
            'header',
            'attendancetotalsrowsheader',
            get_string('rowsheading', 'customcertelement_attendancetotals')
        );
        $mform->setExpanded('attendancetotalsrowsheader');

        foreach (self::get_row_defs() as $rowkey => $def) {
            [$field, ] = $def;
            $mform->addElement('advcheckbox', $field, '', get_string($field, 'customcertelement_attendancetotals'));
            $mform->setDefault($field, 1);
        }

        $mform->addElement(
            'header',
            'attendancetotalscolumnsheader',
            get_string('columnsheading', 'customcertelement_attendancetotals')
        );
        $mform->setExpanded('attendancetotalscolumnsheader');

        foreach (self::get_column_defs() as $colkey => $def) {
            [$field, ] = $def;
            $mform->addElement('advcheckbox', $field, '', get_string($field, 'customcertelement_attendancetotals'));
            $mform->setDefault($field, 1);
        }

        parent::render_form_elements($mform);
    }

    /**
     * Sets the data on the form when editing an existing element.
     *
     * @param \MoodleQuickForm $mform the edit_form instance
     */
    public function definition_after_data($mform) {
        if (!empty($this->get_data())) {
            $info = json_decode($this->get_data());

            foreach (self::get_all_checkbox_fields() as $field) {
                if (isset($info->$field)) {
                    $el = $mform->getElement($field);
                    $el->setValue($info->$field);
                }
            }
        }

        parent::definition_after_data($mform);
    }

    /**
     * This will handle how form data will be saved into the data column in
     * the customcert_elements table.
     *
     * @param \stdClass $data the form data.
     * @return string the json encoded array
     */
    public function save_unique_data($data) {
        $arrtostore = [];

        foreach (self::get_all_checkbox_fields() as $field) {
            $arrtostore[$field] = !empty($data->$field) ? 1 : 0;
        }

        return json_encode($arrtostore);
    }

    /**
     * Handles rendering the element on the pdf.
     *
     * @param \pdf $pdf the pdf object
     * @param bool $preview true if it is a preview, false otherwise
     * @param \stdClass $user the user we are rendering this for
     */
    public function render($pdf, $preview, $user) {
        if (empty($this->get_data())) {
            return;
        }

        $info = json_decode($this->get_data());
        $html = $this->build_table_html($info, $preview, $user);

        if ($html === null) {
            return;
        }

        \mod_customcert\element_helper::render_content($pdf, $this, $html);
    }

    /**
     * Render the element in html.
     *
     * This function is used to render the element when we are using the
     * drag and drop interface to position it.
     *
     * @return string the html
     */
    public function render_html() {
        global $USER;

        if (empty($this->get_data())) {
            return;
        }

        $info = json_decode($this->get_data());
        $html = $this->build_table_html($info, true, $USER);

        if ($html === null) {
            return;
        }

        return \mod_customcert\element_helper::render_html_content($this, $html);
    }

    /**
     * Builds the small report-card-style attendance HTML table.
     *
     * @param \stdClass $info the decoded element settings (showsem1, showpresent, etc.)
     * @param bool $preview true if this is a preview render (uses demo counts)
     * @param \stdClass $user the user to fetch real attendance for (ignored when $preview is true)
     * @return string|null HTML, or null if there's nothing to render
     */
    private function build_table_html($info, $preview, $user) {
        if (!$preview && !\core_component::get_component_directory('mod_attendance')) {
            return \html_writer::tag('em', get_string('modattendancemissing', 'customcertelement_attendancetotals'));
        }

        if ($preview) {
            // Demo data so the template designer and the "preview PDF" have
            // something to look at.
            $summary = [
                'sem1'  => ['p' => 42, 'l' => 2, 'ua' => 1, 'ea' => 0, 'total' => 45],
                'sem2'  => ['p' => 40, 'l' => 1, 'ua' => 0, 'ea' => 1, 'total' => 42],
                'total' => ['p' => 82, 'l' => 3, 'ua' => 1, 'ea' => 1, 'total' => 87],
            ];
        } else {
            $summary = self::get_user_summary($user->id);
        }

        $rows = [];
        foreach (self::get_row_defs() as $rowkey => $def) {
            [$field, $labelkey] = $def;
            if (!empty($info->$field)) {
                $rows[$rowkey] = get_string($labelkey, 'customcertelement_attendancetotals');
            }
        }

        $columns = [];
        foreach (self::get_column_defs() as $colkey => $def) {
            [$field, $headingkey] = $def;
            if (!empty($info->$field)) {
                $columns[$colkey] = get_string($headingkey, 'customcertelement_attendancetotals');
            }
        }

        return self::render_table($rows, $columns, $summary);
    }

    /**
     * Get the colours/sizing this element should use, pulled from this
     * plugin's admin settings, with sensible fallbacks if a site has never
     * touched the settings page.
     *
     * @return \stdClass
     */
    private static function get_display_settings() {
        $defaults = [
            'headingtextcolor' => '#000000',
            'headingbgcolor'   => '#f2f2f2',
            'bodytextcolor'    => '#000000',
            'bordercolor'      => '#999999',
            'labelcolumnwidth' => 25,
        ];

        $config = get_config('customcertelement_attendancetotals');

        $out = new \stdClass();
        foreach ($defaults as $key => $default) {
            $value = isset($config->$key) ? $config->$key : '';
            $out->$key = ($value === '' || $value === false) ? $default : $value;
        }

        // Keep the label column within sane bounds even if someone types a
        // silly value into the settings field.
        $out->labelcolumnwidth = max(10, min(90, (int) $out->labelcolumnwidth));

        return $out;
    }

    /**
     * Build the attendance table: a label column (Semester 1 / Semester 2 /
     * Total row names) down the left, then one fixed-width cell per
     * selected P/L/UA/EA/Total column.
     *
     * Uses an explicit width percentage on every cell (rather than relying
     * on <colgroup>/<col>) because TCPDF's HTML table renderer - which is
     * what actually draws this, via writeHTMLCell() - reliably honours a
     * width set directly on td/th cells.
     *
     * @param array $rows rowkey => row label, in display order
     * @param array $columns colkey => short column heading, in display order
     * @param array $summary rowkey => [p, l, ua, ea, total] counts (as produced by get_user_summary())
     * @return string
     */
    private static function render_table(array $rows, array $columns, array $summary) {
        $settings = self::get_display_settings();

        $labelwidth = $settings->labelcolumnwidth;
        $numcols = max(1, count($columns));
        $colwidth = (100 - $labelwidth) / $numcols;

        $cornerstyle = sprintf(
            'border:0.5pt solid %s;padding:2px 6px;font-weight:bold;background-color:%s;color:%s;width:%s%%;',
            $settings->bordercolor,
            $settings->headingbgcolor,
            $settings->headingtextcolor,
            $labelwidth
        );
        $colheaderstyle = sprintf(
            'border:0.5pt solid %s;padding:2px 6px;font-weight:bold;background-color:%s;color:%s;width:%s%%;',
            $settings->bordercolor,
            $settings->headingbgcolor,
            $settings->headingtextcolor,
            $colwidth
        );
        $rowlabelstyle = sprintf(
            'border:0.5pt solid %s;padding:2px 6px;font-weight:bold;background-color:%s;color:%s;width:%s%%;text-align:left;',
            $settings->bordercolor,
            $settings->headingbgcolor,
            $settings->headingtextcolor,
            $labelwidth
        );
        $valuestyle = sprintf(
            'border:0.5pt solid %s;padding:2px 6px;text-align:center;color:%s;width:%s%%;',
            $settings->bordercolor,
            $settings->bodytextcolor,
            $colwidth
        );

        // Header row.
        $headrow = \html_writer::tag('td', '', ['style' => $cornerstyle]);
        foreach ($columns as $label) {
            $headrow .= \html_writer::tag('td', $label, ['style' => $colheaderstyle]);
        }

        // One data row per selected Semester 1 / Semester 2 / Total.
        $bodyrows = '';
        foreach ($rows as $rowkey => $rowlabel) {
            $datarow = \html_writer::tag('td', $rowlabel, ['style' => $rowlabelstyle]);

            foreach ($columns as $colkey => $label) {
                $value = $summary[$rowkey][$colkey] ?? 0;
                $datarow .= \html_writer::tag('td', $value, ['style' => $valuestyle]);
            }

            $bodyrows .= \html_writer::tag('tr', $datarow);
        }

        $html = \html_writer::start_tag('table', ['style' => 'border-collapse:collapse;width:100%;']);
        $html .= \html_writer::tag('tr', $headrow);
        $html .= $bodyrows;
        $html .= \html_writer::end_tag('table');

        return $html;
    }

    /**
     * Read the configured semester start/end dates.
     *
     * To keep this "one place to set dates" for whoever administers the
     * site, this prefers block_attendance_summary's own semester date
     * settings (if that block is installed and has a given date set),
     * field-by-field, and only falls back to this plugin's own settings
     * fields for whichever fields the block doesn't have set. This means:
     *   - If block_attendance_summary is installed and fully configured,
     *     you only ever need to set dates there - this element picks them
     *     up automatically.
     *   - If that block isn't installed, or a field is blank there, this
     *     element still works using its own settings page.
     *
     * @return array [sem1start, sem1end, sem2start, sem2end, alldatesconfigured]
     */
    private static function get_semester_dates() {
        $blockconfig = get_config('block_attendance_summary');
        $ownconfig = get_config('customcertelement_attendancetotals');

        if ($blockconfig === false) {
            $blockconfig = new \stdClass();
        }
        if ($ownconfig === false) {
            $ownconfig = new \stdClass();
        }

        $pick = function ($field) use ($blockconfig, $ownconfig) {
            if (!empty($blockconfig->$field)) {
                return $blockconfig->$field;
            }
            return $ownconfig->$field ?? '';
        };

        $sem1startraw = $pick('sem1start');
        $sem1endraw   = $pick('sem1end');
        $sem2startraw = $pick('sem2start');
        $sem2endraw   = $pick('sem2end');

        $sem1start = !empty($sem1startraw) ? strtotime($sem1startraw . ' 00:00:00') : 0;
        $sem1end   = !empty($sem1endraw)   ? strtotime($sem1endraw   . ' 23:59:59') : 0;
        $sem2start = !empty($sem2startraw) ? strtotime($sem2startraw . ' 00:00:00') : 0;
        $sem2end   = !empty($sem2endraw)   ? strtotime($sem2endraw   . ' 23:59:59') : 0;

        // strtotime() returns false on unparsable input - treat that as "not set".
        $sem1start = $sem1start ?: 0;
        $sem1end   = $sem1end ?: 0;
        $sem2start = $sem2start ?: 0;
        $sem2end   = $sem2end ?: 0;

        $configured = ($sem1start && $sem1end && $sem2start && $sem2end);

        return [$sem1start, $sem1end, $sem2start, $sem2end, $configured];
    }

    /**
     * Build a summary array for the given user: Semester 1 / Semester 2 /
     * Total counts of Present / Late / Unexcused Absent / Excused Absent,
     * pulled from every mod_attendance activity in every course they're
     * enrolled in.
     *
     * This mirrors block_attendance_summary\local\attendance_helper::get_user_summary()
     * exactly, just returning a nested array (rowkey => [p,l,ua,ea,total])
     * instead of a flattened mustache-friendly array, since that's more
     * convenient for render_table() above.
     *
     * @param int $userid
     * @return array
     */
    private static function get_user_summary($userid) {
        global $DB;

        [$sem1start, $sem1end, $sem2start, $sem2end, ] = self::get_semester_dates();

        $buckets = [
            'sem1'  => ['p' => 0, 'l' => 0, 'ua' => 0, 'ea' => 0, 'total' => 0],
            'sem2'  => ['p' => 0, 'l' => 0, 'ua' => 0, 'ea' => 0, 'total' => 0],
            'total' => ['p' => 0, 'l' => 0, 'ua' => 0, 'ea' => 0, 'total' => 0],
        ];

        // Courses the user is actively enrolled in.
        $courses = enrol_get_users_courses($userid, true, ['id']);
        if (empty($courses)) {
            return $buckets;
        }
        $courseids = array_keys($courses);

        [$insql, $inparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'course');

        // All attendance activity instances that live in those courses.
        $sql = "SELECT a.id
                  FROM {attendance} a
                 WHERE a.course $insql";
        $attendanceids = $DB->get_fieldset_sql($sql, $inparams);

        if (empty($attendanceids)) {
            return $buckets;
        }

        [$ainsql, $ainparams] = $DB->get_in_or_equal($attendanceids, SQL_PARAMS_NAMED, 'att');

        // Preload non-deleted status options (Present/Late/Unexcused/Excused
        // etc.) so we can translate each log entry's statusid into one of
        // our p/l/ua/ea buckets via its acronym.
        $statussql = "SELECT id, attendanceid, setnumber, acronym
                        FROM {attendance_statuses}
                       WHERE attendanceid $ainsql
                             AND (deleted = 0 OR deleted IS NULL)";
        $statuses = $DB->get_records_sql($statussql, $ainparams);

        $statusbucketkey = []; // statusid => 'p'|'l'|'ua'|'ea'|null (unmatched acronym).
        foreach ($statuses as $status) {
            $acronym = strtoupper(trim((string) $status->acronym));
            $statusbucketkey[$status->id] = self::ACRONYM_MAP[$acronym] ?? null;
        }

        // Every session this student has an actual attendance log entry for.
        $params = $ainparams + ['userid' => $userid];
        $sql = "SELECT al.id AS logid, al.statusid, s.id AS sessionid, s.sessdate,
                       s.attendanceid, s.statusset
                  FROM {attendance_log} al
                  JOIN {attendance_sessions} s ON s.id = al.sessionid
                 WHERE al.studentid = :userid
                       AND s.attendanceid $ainsql";
        $records = $DB->get_records_sql($sql, $params);

        foreach ($records as $rec) {
            if (!array_key_exists($rec->statusid, $statusbucketkey)) {
                // Status has since been deleted/changed - skip it, we can't count it fairly.
                continue;
            }

            $bucketkey = $statusbucketkey[$rec->statusid];

            $buckets['total']['total']++;
            if ($bucketkey !== null) {
                $buckets['total'][$bucketkey]++;
            }

            $sessdate = (int) $rec->sessdate;

            if ($sem1start && $sessdate >= $sem1start && $sessdate <= $sem1end) {
                $buckets['sem1']['total']++;
                if ($bucketkey !== null) {
                    $buckets['sem1'][$bucketkey]++;
                }
            } else if ($sem2start && $sessdate >= $sem2start && $sessdate <= $sem2end) {
                $buckets['sem2']['total']++;
                if ($bucketkey !== null) {
                    $buckets['sem2'][$bucketkey]++;
                }
            }
        }

        return $buckets;
    }
}

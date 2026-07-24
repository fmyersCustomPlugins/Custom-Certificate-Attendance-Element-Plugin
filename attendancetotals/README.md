# customcertelement_attendancetotals

A `mod_customcert` element that displays a student's attendance totals -
Semester 1, Semester 2, and Total counts of Present / Late / Unexcused
Absent / Excused Absent - report-card style, on a certificate.

## Requirements

This plugin requires `mod_attendance` to work in order to display attendance correctly

## How it works

- Table layout: a row-label column (Semester 1 / Semester 2 / Total) down
  the left, and a column for each attendance type you choose to show (P,
  L, UA, EA, Total) - matching the exact shape of your dashboard block's
  table.
- Checkboxes let you toggle which **rows** (Semester 1, Semester 2,
  Total) and which **columns** (Present, Late, Unexcused Absent, Excused
  Absent, Total) appear, in case a certificate only needs a Total row, for
  example.
- Every cell has an explicit width percentage, so the table stays a
  uniform size regardless of the numbers shown.
- Colors are set once, site-wide, via **Site administration > Plugins >
  Activity modules > Certificate** (search "attendancetotals" in the
  admin settings search box if you can't find it):
  - Heading text color (row/column labels)
  - Heading background color
  - Value text color
  - Border color
  - Row label column width (%)

## Semester dates

Attendance will be split into Semester 1 / Semester 2 using start/end dates.

**If `block_attendance_summary` is installed and has its semester dates
set**, this element reads them automatically - the system administrator only sets
dates in one place (the block's settings), and this element stays in
sync with no extra work.

This element also has its own semester date fields (same settings page as
the colours, under a "Semester dates (fallback)" heading), used if you do not
already have `block_attendance_summar` installed.

If neither source has a date set, that semester's row will show 0s,
However the Total row is still accurate either way, since it doesn't depend on
semester dates.

## Installation - via the "Install plugins" upload page

1. Log in as an admin and go to **Site administration > Plugins >
   Install plugins**.
2. Upload `customcertelement_attendancetotals.zip`.
3. Moodle will detect it as a certificate element subplugin
   (component `customcertelement_attendancetotals`) automatically.
   Confirm and follow the on-screen install steps.
4. Go to **Site administration > Plugins > Activity modules >
   Certificate** and set your semester dates and colours (see above).
5. Go to a certificate template's editor, choose **Attendance totals**
   from the "Add element" dropdown, and position it. Add it once - not
   once per course.

## Notes / things you may want to adjust

- If `mod_attendance` isn't installed, the element will render a visible
  message instead of failing silently.
- Preview/demo numbers are shown in the template editor and "preview PDF"
  so you can see the layout without needing a real student's data.

<?php

declare(strict_types=1);

namespace app\views\calendar;

use app\libraries\FileUtils;
use app\models\CalendarInfo;
use app\models\User;
use app\views\AbstractView;
use app\views\NavigationView;

class CalendarView extends AbstractView {
    /**
     * This function shows a calendar with arbitrary items. It first shows a calendar view that list all items on
     * calendar by their given date. Then it shows a series of tables to list all items.
     *
     * @param CalendarInfo $info the information used to fill the calendar
     * @return string
     * @see NavigationView::gradeableSections
     */
    public function showCalendar(CalendarInfo $info, bool $in_course = false): string {

        $year = (isset($_GET['year']) && $_GET['year'] != "")  ?  (int) $_GET['year']  : (int) date("Y");
        $month = (isset($_GET['month']) && $_GET['month'] != "") ?  (int) $_GET['month'] : (int) date("n");
        $show_table = (isset($_GET['show_table'])) ? (int) $_GET['show_table'] : 0; // not showing the table by default

        // Error checking
        $month = (int) sprintf("%08d", $month); // remove leading zero

        if ($month < 0 || $month > 12) {
            $month = (int) date("n");
        }
        if ($year < 1970 || $year > 2100) {
            $year = (int) date("Y");
        }

        $this->core->getOutput()->addInternalCss("navigation.css");
        $this->core->getOutput()->addInternalCss('calendar.css');
        $this->core->getOutput()->addInternalJs('calendar.js');
        $this->core->getOutput()->addVendorJs(FileUtils::joinPaths('flatpickr', 'flatpickr.min.js'));
        $this->core->getOutput()->addVendorCss(FileUtils::joinPaths('flatpickr', 'flatpickr.min.css'));
        $this->core->getOutput()->addVendorJs(FileUtils::joinPaths('flatpickr', 'plugins', 'shortcutButtons', 'shortcut-buttons-flatpickr.min.js'));
        $this->core->getOutput()->addVendorCss(FileUtils::joinPaths('flatpickr', 'plugins', 'shortcutButtons', 'themes', 'light.min.css'));
        $this->core->getOutput()->addInternalCss('table.css');
        $this->core->getOutput()->enableMobileViewport();
        $this->core->getOutput()->addBreadcrumb($in_course ? "Course Calendar" : "Calendar");
        return $this->core->getOutput()->renderTwigTemplate("calendar/Calendar.twig", [
            "show_table" => $show_table,
            "view_year" => $year,          // the year that the calendar is viewing
            "view_month" => $month,        // the month that the calendar is viewing
            "curr_year" => date("Y"),  // the current year
            "curr_month" => date("n"), // the current month
            "curr_day" => date("d"),   // the current date
            'date_format' => $this->core->getConfig()->getDateTimeFormat()->getFormat('gradeable'),
            "gradeables_by_date" => $info->getItemsByDate(),
            "gradeables_by_section" => $info->getItemsBySections(),
            "empty_message" => $info->getEmptyMessage(),
            "in_course" => $in_course,
            "is_instructor" => $this->core->getUser()->getGroup() === User::GROUP_INSTRUCTOR,
            "colors" => $info->getColors(),
            "instructor_courses" => $this->core->getQueries()->getInstructorLevelUnarchivedCourses($this->core->getUser()->getId()),
            "view_cookie" => isset($_COOKIE['view']) ? $_COOKIE['view'] : "month"
        ]);
    }
}

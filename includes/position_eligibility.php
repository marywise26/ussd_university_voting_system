<?php
/**
 * Save as:
 * includes/position_eligibility.php
 *
 * Use this in voting and candidate application pages.
 */

function csv_rule_allows(?string $csv, int $value): bool
{
    $csv = trim((string)$csv);

    if ($csv === '') {
        return true;
    }

    $allowed = array_filter(array_map('trim', explode(',', $csv)));

    return in_array((string)$value, $allowed, true);
}

function faculty_rule_allows(?string $requiredFaculty, ?string $studentFaculty): bool
{
    $requiredFaculty = trim((string)$requiredFaculty);

    if ($requiredFaculty === '') {
        return true;
    }

    return $requiredFaculty === trim((string)$studentFaculty);
}

function student_can_vote_for_position(array $student, array $position): bool
{
    if ((int)($student['is_active'] ?? 0) !== 1) {
        return false;
    }

    if (!faculty_rule_allows($position['voter_faculty_code'] ?? null, $student['faculty_code'] ?? null)) {
        return false;
    }

    if (!csv_rule_allows($position['voter_years'] ?? null, (int)($student['study_year'] ?? 1))) {
        return false;
    }

    return true;
}

function student_can_be_candidate_for_position(array $student, array $position): bool
{
    if ((int)($student['is_active'] ?? 0) !== 1) {
        return false;
    }

    if (!faculty_rule_allows($position['candidate_faculty_code'] ?? null, $student['faculty_code'] ?? null)) {
        return false;
    }

    if (!csv_rule_allows($position['candidate_years'] ?? null, (int)($student['study_year'] ?? 1))) {
        return false;
    }

    return true;
}

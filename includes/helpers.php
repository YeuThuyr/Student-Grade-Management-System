<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/languages.php';

function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function calculateAverageScore($midterm, $final, $other)
{
    $midterm = floatval($midterm);
    $final = floatval($final);
    $other = floatval($other);
    return round(($midterm * 0.30) + ($final * 0.50) + ($other * 0.20), 2);
}

function determineLetterGrade($score)
{
    $score = floatval($score);
    if ($score >= 9.0) {
        return 'A+';
    }
    if ($score >= 8.5) {
        return 'A';
    }
    if ($score >= 8.0) {
        return 'B+';
    }
    if ($score >= 7.0) {
        return 'B';
    }
    if ($score >= 6.5) {
        return 'C+';
    }
    if ($score >= 5.5) {
        return 'C';
    }
    if ($score >= 4.0) {
        return 'D';
    }
    return 'F';
}

function gradePoint($letter)
{
    return match ($letter) {
        'A+' => 4.0,
        'A' => 3.7,
        'B+' => 3.5,
        'B' => 3.0,
        'C+' => 2.5,
        'C' => 2.0,
        'D' => 1.0,
        default => 0.0,
    };
}

function gpaStatusLabel($gpa)
{
    if ($gpa >= 8.0)
        return __('gpa_excellent');
    if ($gpa >= 6.5)
        return __('gpa_good');
    if ($gpa >= 5.0)
        return __('gpa_average');
    return __('gpa_weak');
}

function gpaPassFail($gpa)
{
    return $gpa >= 5.0 ? __('gpa_pass') : __('gpa_fail');
}

function redirect($url)
{
    header('Location: ' . $url);
    exit();
}

function getAcademicEndYear($academicYearStr)
{
    $academicYearStr = trim($academicYearStr);
    if (empty($academicYearStr)) {
        return intval(date('Y'));
    }
    
    // Split by non-digit characters to find years/numbers
    $parts = preg_split('/[^\d]+/', $academicYearStr);
    $numbers = array_filter($parts, function($val) {
        return $val !== '';
    });
    
    if (empty($numbers)) {
        return intval(date('Y'));
    }
    
    $numbers = array_values($numbers);
    if (count($numbers) === 1) {
        $year = $numbers[0];
        if (strlen($year) === 2) {
            // Assume 20xx
            return intval('20' . $year);
        }
        return intval($year);
    }
    
    // We have at least two numbers (e.g., 2023 and 2024, or 2023 and 24)
    $startYearStr = $numbers[0];
    $endYearStr = $numbers[1];
    
    if (strlen($endYearStr) === 2 && strlen($startYearStr) === 4) {
        // e.g. "2023-24" -> "2024"
        $century = substr($startYearStr, 0, 2);
        return intval($century . $endYearStr);
    }
    
    return intval($endYearStr);
}

function isGradeLocked($academicYearStr)
{
    $currentYear = intval(date('Y'));
    $endYear = getAcademicEndYear($academicYearStr);
    return ($currentYear - $endYear) > 1;
}

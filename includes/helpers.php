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

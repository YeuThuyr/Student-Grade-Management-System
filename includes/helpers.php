<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function calculateAverageScore($midterm, $final, $other)
{
    $midterm = floatval($midterm);
    $final = floatval($final);
    $other = floatval($other);
    return round(($midterm + $final + $other) / 3, 2);
}

function determineLetterGrade($score)
{
    if ($score >= 9.0) {
        return 'A+';
    }
    if ($score >= 8.0) {
        return 'A';
    }
    if ($score >= 7.0) {
        return 'B+';
    }
    if ($score >= 6.5) {
        return 'B';
    }
    if ($score >= 5.0) {
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
        'A' => 4.0,
        'B+' => 3.5,
        'B' => 3.0,
        'C' => 2.0,
        'D' => 1.0,
        default => 0.0,
    };
}

function gpaStatusLabel($gpa)
{
    if ($gpa >= 8.0)
        return 'Xuất sắc';
    if ($gpa >= 6.5)
        return 'Khá';
    if ($gpa >= 5.0)
        return 'Trung bình';
    return 'Yếu';
}

function gpaPassFail($gpa)
{
    return $gpa >= 5.0 ? 'Đạt' : 'Trượt';
}

function redirect($url)
{
    header('Location: ' . $url);
    exit();
}

<?php
declare(strict_types=1);

/**
 * Loader dữ liệu cho view.
 *
 * Tách theo nhóm để giảm xung đột khi làm song song:
 *   common.php     helper dùng chung
 *   shared.php     dữ liệu nhiều role cùng dùng
 *   admin.php      dữ liệu riêng admin
 *   customer.php   dữ liệu riêng hội viên
 *   instructor.php dữ liệu riêng giáo viên
 *
 * View chỉ cần: require_once __DIR__ . '/../data/data.php';
 */

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/shared.php';
require_once __DIR__ . '/admin.php';
require_once __DIR__ . '/customer.php';
require_once __DIR__ . '/instructor.php';

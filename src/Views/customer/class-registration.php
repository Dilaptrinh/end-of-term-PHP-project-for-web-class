<?php
require_once __DIR__ . '/../data/data.php';

$classes = data_classes();
$open = array_values(array_filter($classes, static fn ($c) => $c['status'] === 'dang_mo'));
$instructors = array_values(array_unique(array_map(static fn ($c) => $c['instructor'], $open)));
?>
<div class="page-head">
    <div>
        <h1>Đăng ký lớp học</h1>
        <p>Chọn lớp phù hợp với lịch của bạn. Gói Premium còn hiệu lực đến 12/02/2027.</p>
    </div>
    <div class="page-head-actions">
        <a class="btn btn-outline-primary" href="<?= e(url('/customer/schedule')) ?>"><?= icon('calendar-week') ?> Lịch của tôi</a>
    </div>
</div>

<div class="alert alert-info d-flex align-items-center gap-2 mb-3" role="note">
    <?= icon('info-circle') ?>
    <div class="flex-grow-1">Bạn đang dùng <strong>Premium 6 tháng</strong> — được đăng ký không giới hạn lớp nhóm.</div>
    <a class="btn btn-sm btn-outline-primary" href="<?= e(url('/customer/history')) ?>">Xem gói</a>
</div>

<form class="filter-bar" method="get" action="<?= e(url('/customer/class-registration')) ?>">
    <div class="filter-field filter-search">
        <label class="form-label" for="cls-q">Tìm lớp / giáo viên</label>
        <div class="position-relative">
            <input class="form-control" id="cls-q" name="q" type="search" placeholder="VD: Yoga, Hà…">
        </div>
    </div>
    <div class="filter-field">
        <label class="form-label" for="cls-day">Thứ</label>
        <select class="form-select" id="cls-day" name="weekday">
            <option value="">Tất cả</option>
            <?php for ($d = 1; $d <= 7; $d++): ?>
                <option value="<?= $d ?>"><?= e(weekday($d)) ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="filter-field">
        <label class="form-label" for="cls-teacher">Giáo viên</label>
        <select class="form-select" id="cls-teacher" name="instructor">
            <option value="">Tất cả</option>
            <?php foreach ($instructors as $teacher): ?>
                <option value="<?= e($teacher) ?>"><?= e($teacher) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-field">
        <label class="form-label" for="cls-level">Trình độ</label>
        <select class="form-select" id="cls-level" name="level">
            <option value="">Tất cả</option>
            <option>Cơ bản</option>
            <option>Trung cấp</option>
            <option>Nâng cao</option>
            <option>Mọi trình độ</option>
        </select>
    </div>
    <div class="filter-field" style="min-width:auto;">
        <label class="form-label">&nbsp;</label>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><?= icon('search') ?> Tìm</button>
            <a class="btn btn-ghost" href="<?= e(url('/customer/class-registration')) ?>">Xóa lọc</a>
        </div>
    </div>
</form>

<div class="d-flex align-items-center flex-wrap gap-2 mb-3">
    <div class="text-small text-muted-2">Tìm thấy <strong class="text-brand"><?= count($open) ?></strong> lớp đang mở</div>
    <div class="ms-auto d-flex align-items-center gap-2">
        <label class="form-check form-switch mb-0 d-flex align-items-center gap-2">
            <input class="form-check-input" type="checkbox" id="only-available" checked>
            <span class="form-check-label text-small">Chỉ lớp còn chỗ</span>
        </label>
    </div>
</div>

<div class="row g-3" id="class-grid">
    <?php foreach ($open as $class):
        $available = $class['capacity'] - $class['enrolled'];
        $percent = (int) round($class['enrolled'] / max(1, $class['capacity']) * 100);
        $tone = $available === 0 ? 'red' : ($percent >= 80 ? 'amber' : '');
    ?>
        <div class="col-md-6 col-xl-4" data-class-card data-available="<?= $available > 0 ? '1' : '0' ?>">
            <article class="class-card">
                <div class="class-card-top">
                    <div class="day-chip">
                        <strong><?= e(weekday_short($class['weekday'])) ?></strong>
                        <span><?= e($class['start']) ?></span>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-start gap-2">
                            <h3 class="flex-grow-1"><?= e($class['name']) ?></h3>
                            <span class="badge-soft"><?= e($class['level']) ?></span>
                        </div>
                        <div class="teacher"><?= icon('person') ?> <?= e($class['instructor']) ?></div>
                    </div>
                </div>
                <div class="class-card-body">
                    <div class="class-meta">
                        <div class="item"><?= icon('clock') ?> <?= e($class['start'] . ' – ' . $class['end']) ?></div>
                        <div class="item"><?= icon('geo-alt') ?> <?= e($class['room']) ?></div>
                        <div class="item"><?= icon('calendar3') ?> <?= e(weekday($class['weekday'])) ?></div>
                        <div class="item"><?= icon('people') ?> <?= e((string) $class['enrolled'] . '/' . (string) $class['capacity']) ?></div>
                    </div>
                    <div class="class-capacity">
                        <span><?= $available === 0 ? 'Lớp đã đầy' : 'Còn ' . $available . ' chỗ' ?></span>
                        <span><?= $percent ?>%</span>
                    </div>
                    <?= progress_bar($percent, $tone) ?>
                    <p class="text-muted-2 text-tiny mt-2 mb-0"><?= e($class['desc']) ?></p>
                </div>
                <div class="class-card-foot">
                    <span class="availability <?= $available === 0 ? 'text-danger' : 'text-success' ?>">
                        <?= $available === 0 ? 'Hết chỗ' : 'Còn ' . $available . ' chỗ' ?>
                    </span>
                    <?php if ($available === 0): ?>
                        <button class="btn btn-outline-secondary btn-sm" type="button" disabled>Đã đầy</button>
                    <?php else: ?>
                        <button class="btn btn-primary btn-sm" type="button"
                                data-bs-toggle="modal" data-bs-target="#registerClassModal"
                                data-class-name="<?= e($class['name']) ?>"
                                data-class-meta="<?= e(weekday($class['weekday']) . ' · ' . $class['start'] . ' – ' . $class['end'] . ' · ' . $class['room']) ?>"
                                data-class-teacher="<?= e($class['instructor']) ?>">
                            Đăng ký
                        </button>
                    <?php endif; ?>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>

<div class="app-card mt-3">
    <?= pagination(count($open), 1, 12, 'lớp học') ?>
</div>

<div class="modal fade" id="registerClassModal" tabindex="-1" aria-labelledby="registerClassTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="registerClassTitle">Xác nhận đăng ký lớp</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="stat-icon"><?= icon('journal-plus') ?></span>
                    <div>
                        <div class="fw-800" data-modal-class-name>Yoga cơ bản</div>
                        <div class="text-tiny text-muted-2" data-modal-class-meta></div>
                    </div>
                </div>
                <div class="kv-list mb-3">
                    <div class="kv"><span>Giáo viên</span><span data-modal-class-teacher></span></div>
                    <div class="kv"><span>Gói đang dùng</span><span>Premium 6 tháng</span></div>
                    <div class="kv"><span>Học phí lớp</span><span class="text-success">Đã bao gồm trong gói</span></div>
                </div>
                <div class="alert alert-warning mb-0 d-flex gap-2">
                    <?= icon('exclamation-triangle') ?>
                    <span class="text-tiny">Vui lòng có mặt trước giờ học 10 phút. Hủy đăng ký trước 12 giờ để không bị tính vắng.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Hủy</button>
                <form >
                    <button type="submit" class="btn btn-primary"><?= icon('check-lg') ?> Xác nhận đăng ký</button>
                </form>
            </div>
        </div>
    </div>
</div>

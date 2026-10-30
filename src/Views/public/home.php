<?php
require_once __DIR__ . '/../data/data.php';

$packages = data_packages();
$classes = data_classes();
$featuredClasses = array_slice($classes, 0, 3);
?>
<section class="hero">
    <div class="hero-inner">
        <div>
            <span class="hero-badge"><?= icon('stars') ?> Phòng tập &amp; lớp học hiện đại</span>
            <h1>Tập luyện thông minh.<br><span class="accent">Sống khỏe mỗi ngày.</span></h1>
            <p class="lead">FitZone mang đến trải nghiệm tập luyện cá nhân hóa với hơn 20 lớp học mỗi tuần, giáo viên chuyên nghiệp và hệ thống theo dõi tiến độ trực quan.</p>
            <div class="hero-actions">
                <a class="btn btn-light-brand btn-lg" href="<?= e(url('/register')) ?>">
                    Đăng ký hội viên <?= icon('arrow-right', 'ms-1') ?>
                </a>
                <a class="btn btn-outline-light btn-lg" href="<?= e(url('/#packages')) ?>">Xem gói tập</a>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <div class="num">1.200+</div>
                    <div class="lbl">Hội viên đang tập luyện</div>
                </div>
                <div class="stat">
                    <div class="num">24</div>
                    <div class="lbl">Lớp học mỗi tuần</div>
                </div>
                <div class="stat">
                    <div class="num">18</div>
                    <div class="lbl">Huấn luyện viên</div>
                </div>
            </div>
        </div>

        <div class="hero-visual" aria-hidden="true">
            <div class="hero-visual-card">
                <div class="hvc-head">
                    <span class="dot"></span>
                    <span class="hvc-title">Lớp học hôm nay</span>
                    <span class="ms-auto text-tiny" style="color:rgba(255,255,255,.6)"><?= icon('calendar3') ?> 24/10</span>
                </div>
                <div class="mini-class">
                    <span class="time">06:00</span>
                    <span>
                        <span class="name d-block">HIIT đốt mỡ</span>
                        <span class="meta">Nguyễn Đức Anh · P.202</span>
                    </span>
                    <span class="pill">Còn 2 chỗ</span>
                </div>
                <div class="mini-class">
                    <span class="time">09:30</span>
                    <span>
                        <span class="name d-block">Yoga nâng cao</span>
                        <span class="meta">Trần Thu Hà · P.101</span>
                    </span>
                    <span class="pill">Còn 4 chỗ</span>
                </div>
                <div class="mini-class">
                    <span class="time">16:00</span>
                    <span>
                        <span class="name d-block">Cardio &amp; Core</span>
                        <span class="meta">Lê Ngọc Mai · P.202</span>
                    </span>
                    <span class="pill" style="background:rgba(253,224,71,.18);color:#FDE68A;">Sắp đầy</span>
                </div>
                <div class="mini-class">
                    <span class="time">18:00</span>
                    <span>
                        <span class="name d-block">Yoga cơ bản</span>
                        <span class="meta">Trần Thu Hà · P.101</span>
                    </span>
                    <span class="pill">Còn 6 chỗ</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="public-section" id="features">
    <div class="public-section-head">
        <div class="eyebrow">Vì sao chọn FitZone</div>
        <h2>Mọi thứ bạn cần cho hành trình khỏe mạnh</h2>
        <p>Từ đăng ký lớp học, theo dõi tiến độ đến quản lý gói tập — tất cả trong một nền tảng duy nhất.</p>
    </div>
    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <div class="feature-card">
                <div class="feat-icon"><?= icon('calendar2-week') ?></div>
                <h3>Lớp học đa dạng</h3>
                <p>Yoga, Pilates, HIIT, Boxing, Zumba và Gym với lịch linh hoạt mỗi tuần.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="feature-card">
                <div class="feat-icon"><?= icon('person-badge') ?></div>
                <h3>Giáo viên chuyên nghiệp</h3>
                <p>Đội ngũ huấn luyện viên giàu kinh nghiệm, theo sát từng học viên.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="feature-card">
                <div class="feat-icon"><?= icon('calendar-week') ?></div>
                <h3>Lịch tập rõ ràng</h3>
                <p>Xem lịch lớp, giáo viên và phòng tập trong một giao diện dễ theo dõi.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="feature-card">
                <div class="feat-icon"><?= icon('shield-check') ?></div>
                <h3>Quản lý minh bạch</h3>
                <p>Gói tập, đăng ký lớp và thông tin hội viên rõ ràng cho người dùng.</p>
            </div>
        </div>
    </div>
</section>

<section class="public-section pt-0" id="packages">
    <div class="public-section-head">
        <div class="eyebrow">Gói tập</div>
        <h2>Chọn gói phù hợp với bạn</h2>
        <p>Linh hoạt theo thời hạn, nâng cấp bất cứ lúc nào.</p>
    </div>
    <div class="row g-3">
        <?php foreach (array_slice($packages, 0, 4) as $index => $package): ?>
            <div class="col-md-6 col-lg-3">
                <div class="price-card<?= $index === 2 ? ' featured' : '' ?>">
                    <?php if ($index === 2): ?><span class="ribbon">Phổ biến nhất</span><?php endif; ?>
                    <h3><?= e($package['name']) ?></h3>
                    <p class="text-muted-2 text-small mb-0">Thời hạn <?= e((string) $package['days']) ?> ngày</p>
                    <div class="price"><?= e(number_format((float) $package['price'], 0, ',', '.')) ?><small>đ</small></div>
                    <ul>
                        <li><span class="tick"><?= icon('check-circle-fill') ?></span> Truy cập phòng tập không giới hạn</li>
                        <?php if ($package['days'] >= 90): ?>
                            <li><span class="tick"><?= icon('check-circle-fill') ?></span> Tham gia lớp nhóm không giới hạn</li>
                        <?php endif; ?>
                        <?php if ($package['days'] >= 180): ?>
                            <li><span class="tick"><?= icon('check-circle-fill') ?></span> 1 buổi PT và tư vấn dinh dưỡng</li>
                        <?php endif; ?>
                    </ul>
                    <a class="btn btn-primary w-100" href="<?= e(url('/register')) ?>">Chọn gói này</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="public-section pt-0" id="classes">
    <div class="public-section-head">
        <div class="eyebrow">Lớp học nổi bật</div>
        <h2>Bắt đầu với lớp bạn yêu thích</h2>
        <p>Sức chứa có hạn, đăng ký sớm để giữ chỗ.</p>
    </div>
    <div class="row g-3">
        <?php foreach ($featuredClasses as $class): ?>
            <div class="col-md-6 col-lg-4">
                <article class="class-card">
                    <div class="class-card-top">
                        <div class="day-chip">
                            <strong><?= e(weekday_short($class['weekday'])) ?></strong>
                            <span><?= e($class['start']) ?></span>
                        </div>
                        <div>
                            <h3><?= e($class['name']) ?></h3>
                            <div class="teacher"><?= icon('person') ?> <?= e($class['instructor']) ?></div>
                        </div>
                    </div>
                    <div class="class-card-body">
                        <div class="class-meta">
                            <div class="item"><?= icon('clock') ?> <?= e($class['start'] . ' – ' . $class['end']) ?></div>
                            <div class="item"><?= icon('geo-alt') ?> <?= e($class['room']) ?></div>
                            <div class="item"><?= icon('bar-chart') ?> <?= e($class['level']) ?></div>
                            <div class="item"><?= icon('people') ?> <?= e((string) $class['enrolled'] . '/' . (string) $class['capacity']) ?></div>
                        </div>
                        <p class="text-muted-2 text-small mb-0"><?= e($class['desc']) ?></p>
                    </div>
                    <div class="class-card-foot">
                        <span class="availability text-success">Còn <?= e((string) ($class['capacity'] - $class['enrolled'])) ?> chỗ</span>
                        <a class="btn btn-outline-primary btn-sm" href="<?= e(url('/login')) ?>">Đăng ký</a>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section style="background:var(--brand-50);">
    <div class="public-section" style="padding-top:2.5rem;padding-bottom:2.5rem;">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div>
                <h2 class="mb-1" style="font-size:1.45rem;">Sẵn sàng bắt đầu?</h2>
                <p class="text-muted-2 mb-0">Tạo tài khoản miễn phí, chọn lớp học đầu tiên và bắt đầu hành trình của bạn.</p>
            </div>
            <div class="ms-auto d-flex gap-2 flex-wrap">
                <a class="btn btn-primary btn-lg" href="<?= e(url('/register')) ?>">Đăng ký ngay</a>
                <a class="btn btn-outline-primary btn-lg" href="<?= e(url('/login')) ?>">Đăng nhập</a>
            </div>
        </div>
    </div>
</section>

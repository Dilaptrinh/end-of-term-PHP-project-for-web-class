<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tieuDe) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        label { display: block; margin: 8px 0; }
        .loi { color: #b00; margin: 8px 0; }
        .btn { padding: 6px 12px; }
    </style>
</head>
<body>
    <h1><?= e($tieuDe) ?></h1>

    <?php if (!empty($errors)): ?>
        <ul class="loi">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="<?= e($action) ?>">
        <label>
            Tên:
            <input type="text" name="name" value="<?= e($duLieu['name'] ?? '') ?>">
        </label>
        <label>
            Tuổi:
            <input type="number" name="age" value="<?= e($duLieu['age'] ?? '') ?>">
        </label>

        <button class="btn" type="submit">Lưu</button>
        <a href="<?= e(url('/users')) ?>">Hủy</a>
    </form>
</body>
</html>

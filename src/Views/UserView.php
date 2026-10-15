<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách User</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        table { border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #999; padding: 6px 12px; }
        th { background: #eee; }
        .btn { padding: 4px 10px; text-decoration: none; border: 1px solid #333; background: #f5f5f5; color: #000; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Danh sách User</h1>

    <a class="btn" href="<?= e(url('/users/create')) ?>">+ Thêm user</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Tuổi</th>
            <th>Hành động</th>
        </tr>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= e($u->getId()) ?></td>
                <td><?= e($u->getName()) ?></td>
                <td><?= e($u->getAge()) ?></td>
                <td>
                    <a class="btn" href="<?= e(url('/users/edit/' . $u->getId())) ?>">Sửa</a>
                    <form method="post" action="<?= e(url('/users/delete/' . $u->getId())) ?>"
                          style="display:inline"
                          onsubmit="return confirm('Xóa user này?')">
                        <button class="btn" type="submit">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

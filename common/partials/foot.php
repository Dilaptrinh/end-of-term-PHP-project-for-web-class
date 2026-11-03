<?php
/** @var array<int,string> $pageScripts */
$pageScripts = $pageScripts ?? [];
?>
<script src="<?= e(url('/common/assets/vendor/bootstrap/bootstrap.bundle.min.js')) ?>"></script>
<script>
    window.APP_BASE = <?= json_encode(base_url(), JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= e(url('/common/assets/js/api.js')) ?>?v=5"></script>
<script src="<?= e(url('/common/assets/js/ui.js')) ?>?v=6"></script>
<?php foreach ($pageScripts as $script): ?>
    <script src="<?= e(url('/common/assets/js/pages/' . $script . '?v=6')) ?>"></script>
<?php endforeach; ?>
</body>
</html>

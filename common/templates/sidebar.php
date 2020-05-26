<script>
<?php foreach($settings as $name => $value): ?>
<?= $name; ?> = <?= json_encode($value); ?>;
<?php endforeach; ?>
</script>
<script async src="https://<?= $plugin->getMainDomain(); ?>/js/sidebar.js"></script>

<?php
$this->layout->set_title('Le journal de la ville');
$this->layout->ajouter_javascript('gazette.js');
?>

<?= $this->load->view('gazette/article.php') ?>

<!-- Anciens articles -->
<p class="clearfloat centre margin">Anciens articles : <?= $pagination ?></p>

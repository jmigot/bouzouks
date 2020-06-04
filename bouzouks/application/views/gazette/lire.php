<?php
$this->layout->set_title('Le journal de la ville');
$article_id = $gazette[Bouzouk::Gazette_Article]->id;
?>

<!-- Menu -->
<?php $this->load->view('gazette/menu', array('lien' => 1, 'article_id' => $article_id)) ?>

<?= $this->load->view('gazette/article.php') ?>

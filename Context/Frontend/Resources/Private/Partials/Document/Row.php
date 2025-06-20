<?php
  $ext = strtolower(pathinfo($document,PATHINFO_EXTENSION));
  $name = basename($document);  
?>

<div class="d-flex p-2 bg-light border-bottom-dark">
  <span class="col-10"><?php echo $name; ?></span>
  <a href="?controller=project&action=file&project={project.uid}&file=<?php echo $name; ?>" target="_blank" class="mi mi-outline me-2">visibility</a>
  <a href="?controller=project&action=download&project={project.uid}&file=<?php echo $name; ?>" download class="mi mi-outline me-2">save</a>
</div>

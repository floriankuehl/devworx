<form 
  method="POST" 
  enctype="multipart/form-data" 
  action="?controller={controller}&action={action}"
  class="d-flex gap-3 bg-light p-3 border"
>
  <div class="d-none">
    <input type="hidden" name="project" value="{project.uid}">
  </div>
  <div>
    <input type="file" name="documents[]" multiple size="10" required>
  </div>
  <div>
    <input type="submit" value="Upload" class="btn btn-primary">
  </div>
</form>
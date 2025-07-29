<nav class="d-flex flex-row flex-wrap p-2 gap-2">
	<a class="btn btn-secondary" href="/frontend/">Frontend</a>
	<a class="btn btn-secondary" href="/development/">Development</a>
	<a class="btn btn-secondary" href="/development/?controller=backend&action=files">Files</a>
	<a class="btn btn-secondary" href="/documentation/">Documentation</a>
	<div class="flex-grow-1">&nbsp;</div>
	<a class="btn text-bg-primary mi mi-outline" href="?controller=Cache&action=flush&cache=Cascade&context=Devworx">settings</a>
	<form method="GET" class="d-flex flex-row px-2">
		<div class="d-none">
			<input type="hidden" name="controller" value="Cache">
			<input type="hidden" name="action" value="flush">
		</div>
		<select name="cache">
			<option value="" disabled>Alle</option>
			<optgroup label="System">
				<?php forEach( \Devworx\Caches::ids() as $id ): ?>
					<option value="<?= $id ?>"><?= $id ?></option>
				<?php endForEach; ?>
			</optgroup>
			<optgroup label="Other">
				<option value="Models" disabled>Models</option>
				<option value="Documentation">Documentation</option>
			</optgroup>
		</select>
		<button type="submit" class="btn btn-secondary">Flush</button>
	</form>	
	<a class="btn btn-secondary" href="?controller=user&action=profile">Profile</a>
	<a class="btn btn-secondary" href="?controller=user&action=logout">Logout</a>
</nav>

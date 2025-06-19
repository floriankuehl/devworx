<devworx-table 
	name="<?= $table ?>"
	context="<?= $context ?>"
	properties="<?= count($info['properties']) ?>"
	controller="<?= is_array($info['controller']) && $info['controller']['classExists'] ? 1 : 0 ?>"
	actions="<?= count($info['actions']) ?>"
	model="<?= is_array($info['model']) && $info['model']['classExists'] ? 1 : 0 ?>"
	repository="<?= is_array($info['repository']) && $info['repository']['classExists'] ? 1 : 0 ?>"
	template="<?= is_array($info['template']) && $info['template']['fileExists'] ? 1 : 0 ?>"
></devworx-table>
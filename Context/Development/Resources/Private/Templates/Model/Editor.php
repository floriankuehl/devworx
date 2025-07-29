<?php namespace Devworx; ?>
<style>
	devworx-board { display:block; height: 95vh; margin-block: 2rem; background-color: inherit; }
	devworx-property, devworx-relation, devworx-action { 
		max-width: 100%; 
		border: 1px solid transparent !important;
	}
	
	devworx-node { background-color: #888888; color: #fff; }
	
	input[type="text"],
	input[type="number"],
	select { 
		width:100%;
		max-width: 100%;
		border: 1px solid var(--bs-light);
		padding: .2rem .5rem;
	}
		
	devworx-property.active * { background-color: inherit !important; }
	devworx-relation.active * { background-color: inherit !important; }
	devworx-action.active * { background-color: inherit !important; }
	
	devworx-table { cursor:pointer; border: 2px solid transparent }
	devworx-table:hover { border: 2px solid var(--bs-light) }
	
	devworx-node[selected] { border: 1px solid #00f !important }
	devworx-property[selected] { background-color: var(--bs-dark) !important }
	devworx-relation[selected] { background-color: var(--bs-dark) !important }
	devworx-action[selected] { background-color: var(--bs-dark) !important }
</style>
<nav class="d-flex flex-row top-0 start-0">
	<button class="btn btn-secondary" id="createNode">Create Node</button>
	<button class="btn btn-secondary" id="checkBoard">Check</button>
	<!-- <button class="btn btn-secondary" id="saveBoard">Save</button> -->
</nav>
<div class="d-flex flex-column gap-2 px-3">
<devworx-list type="Table" class="d-flex flex-row flex-wrap">
<?php
	forEach( $context->get('tables') as $ctx => $list ){
		foreach( $list as $table => $info ){
			$noController = ( $info['controller'] === false ) || ( $info['controller']['fileExists'] === false );
			$noModel = ( $info['model'] === false ) || ( $info['model']['fileExists'] === false );
			$noRepository = ( $info['repository'] === false ) || ( $info['repository']['fileExists'] === false );
			
			if( $noController && $noModel && $noRepository ) continue;
			echo View::Partial('TablePreview',[
				'table' => $table,
				'ctx' => $ctx,
				'info' => $info
			]);
		}
	}
?>
</devworx-list>
</div>
<script type="module" src="/resources/development/Scripts/setup.js"></script>
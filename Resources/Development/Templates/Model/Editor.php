<style>
	devworx-board { display:block; height: 95vh; margin-block: 2rem }
	devworx-property, devworx-relation, devworx-action { 
		max-width: 100%; 
		border: 1px solid transparent !important;
	}
	
	input[type="text"],
	input[type="number"],
	select { 
		width:100%;
		max-width: 100%;
		border: 1px solid var(--bs-dark) !important;
		padding: .2rem .5rem;
	}
		
	devworx-property.active * { background-color: inherit !important; }
	devworx-relation.active * { background-color: inherit !important; }
	devworx-action.active * { background-color: inherit !important; }
	
	devworx-node[selected] { border: 1px solid #00f !important }
	devworx-property[selected] { background-color: var(--bs-dark) !important }
	devworx-relation[selected] { background-color: var(--bs-dark) !important }
	devworx-action[selected] { background-color: var(--bs-dark) !important }
</style>
<nav class="d-flex flex-row top-0 start-0">
	<button class="btn btn-secondary" id="createNode">Create Node</button>
	<button class="btn btn-secondary" id="saveBoard">Save</button>
	<button class="btn btn-secondary" id="loadBoard">Load</button>
</nav>
<script type="module" src="../Resources/Scripts/Development/setup.js"></script>
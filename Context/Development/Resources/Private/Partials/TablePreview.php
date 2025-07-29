<devworx-table 
	name="{table}"
	context="{ctx}"
	properties="{count(info.properties)}"
	controller="{info.controller.classExists ? 1 : 0}"
	actions="{count(info.actions)}"
	model="{info.model.classExists ? 1 : 0}"
	repository="{info.repository.classExists ? 1 : 0}"
	template="{info.template.fileExists ? 1 : 0}"
></devworx-table>
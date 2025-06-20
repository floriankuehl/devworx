import ElementUtility from '/resources/devworx/Scripts/ElementUtility.js'

export default class RelationUtility {
	
	static cols = [2,2,2,3,1,2]
	
	static css(index){
		return `col-${this.cols[index]}`
	}
	
	static item(label,index){
		return ElementUtility.create(
			'div',
			label,
			['text-bg-dark','text-center',this.css(index)]
		)
	}
	
	static header(){
		return [
			this.item('Table',0),
			this.item('Field',1),
			this.item('Target Field',2),
			this.item('Type',3),
			this.item('Null?',4),
			this.item('...',5)
		]
	}
		
	static instance(data){
		return Relation.createElement((item)=>{
			item.classList.add('d-flex','flex-row')
			if( data ) item.setAttributes(data)
			return item
		})
	}
	
	static map(rows){
		return rows ? rows.map(row=>this.instance(...row)) : rows
	}
	
	static get relationTypeOptions(){
		return [
			ElementUtility.option('1:1'),
			ElementUtility.option('1:n'),
			ElementUtility.option('n:1'),
			ElementUtility.option('n:m')
		]
	}
	
	static Ask(table=null,sourceField=null,targetField=null,relationType=null,nullable=null){
		if( table === null ) table = prompt('Target Table')
		if( sourceField === null ) sourceField = prompt('Source field name')
		if( targetField === null ) targetField = prompt('Target field name')
		if( relationType === null ) relationType = prompt('Relation type','1:n')	
		if( nullable === null ) nullable = confirm('Nullable?')
		
		return Relation.createElement((item)=>{
			item.setAttribute('table',table)
			item.setAttribute('sourceField',sourceField)
			item.setAttribute('targetField',targetField)
			item.setAttribute('relationType',relationType)
			item.setAttribute('nullable',nullable?1:0)
		})
	}
}

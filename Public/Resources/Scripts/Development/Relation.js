import CustomElement from '../Devworx/CustomElement.js'
import List from '../Devworx/Elements/List.js'
import ElementUtility from '../Devworx/ElementUtility.js'

export class RelationUtility {
	
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
			item.setAttribute('table',data.table)
			item.setAttribute('sourceField',data.sourceField)
			item.setAttribute('targetField',data.targetField)
			item.setAttribute('relationType',data.relationType),
			item.setAttribute('nullable',data.nullable)
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

export class Relation extends CustomElement(HTMLElement) {
	#table
	#sourceField
	#targetField
	#relationType
	#nullable
	#controls
	
	get table(){ return this.#table.value }
	set table(value){ this.#table.value = value }
	
	get sourceField(){ return this.#sourceField.value }
	set sourceField(value){ this.#sourceField.value = value }
	
	get targetField(){ return this.#targetField.value }
	set targetField(value){ this.#targetField.value = value }
	
	get relationType(){ return this.#relationType.value }
	set relationType(value){ this.#relationType.value = value }
	
	get nullable(){ return this.#nullable.value }
	set nullable(value){ this.#nullable.value = value }
	
	get controls(){ return this.#controls }
	set controls(value){ this.#controls = value }
	
	constructor(){
		super()
		
		this.#table = ElementUtility.create('input',{type:'text'})
		this.#sourceField = ElementUtility.create('input',{type:'text'})
		this.#targetField = ElementUtility.create('input',{type:'text'})
		this.#nullable = ElementUtility.create('input',{type:'checkbox',value:1})
		this.#relationType = ElementUtility.create('select', RelationUtility.relationTypeOptions)
		this.#controls = ElementUtility.create('div','',['d-flex','flex-row'])
	}
	
	init(){
		super.init()
		
		if( this.hasAttribute('table') )
			this.table = this.getAttribute('table')
		if( this.hasAttribute('sourceField') )
			this.sourceField = this.getAttribute('sourceField')
		if( this.hasAttribute('targetField') )
			this.targetField = this.getAttribute('targetField')
		if( this.hasAttribute('relationType') )
			this.relationType = this.getAttribute('relationType')
		if( this.hasAttribute('nullable') )
			this.nullable = this.getAttribute('nullable')
		
		this.append(
			ElementUtility.create('div',this.#table,['p-1',RelationHeader.css(0)]),
			ElementUtility.create('div',this.#sourceField,['p-1',RelationHeader.css(1)]),
			ElementUtility.create('div',this.#targetField,['p-1',RelationHeader.css(2)]),
			ElementUtility.create('div',this.#relationType,['p-1',RelationHeader.css(3)]),
			ElementUtility.create('div',this.#nullable,['p-1',RelationHeader.css(4)]),
			ElementUtility.create('div',this.#controls,['p-1',RelationHeader.css(5)])
		)
	}
	
	get value(){
		return {
			table: this.#table.value,
			sourceField: this.#sourceField.value,
			targetField: this.#targetField.value,
			relationType: this.#relationType.value,
			nullable: this.#nullable.checked
		}
	}
	
	json(){
		return JSON.stringify(this.value)
	}
}

export class Relations extends List {
	constructor(){
		super()
	}
	
	init(){
		super.init()
		this.setAttribute('type','Relation')
		return this
	}
	
	load(relations=undefined){
		if( relations )
			this.append(...RelationUtility.map(relations))
	}
	
	get value(){
		return [...this.querySelectorAll('devworx-relation')].map(p=>p.value)
	}
}
import CustomElement from '../Devworx/CustomElement.js'
import ElementUtility from '../Devworx/ElementUtility.js'
import RelationUtility from './RelationUtility.js'

export default class Relation extends CustomElement(HTMLElement) {
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
			ElementUtility.create('div',this.#table,['p-1',RelationUtility.css(0)]),
			ElementUtility.create('div',this.#sourceField,['p-1',RelationUtility.css(1)]),
			ElementUtility.create('div',this.#targetField,['p-1',RelationUtility.css(2)]),
			ElementUtility.create('div',this.#relationType,['p-1',RelationUtility.css(3)]),
			ElementUtility.create('div',this.#nullable,['p-1',RelationUtility.css(4)]),
			ElementUtility.create('div',this.#controls,['p-1',RelationUtility.css(5)])
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


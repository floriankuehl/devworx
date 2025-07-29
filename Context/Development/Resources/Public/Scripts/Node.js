import CustomElement from '/resources/devworx/Scripts/CustomElement.js'
import ElementUtility from '/resources/devworx/Scripts/ElementUtility.js'

import PropertyUtility from './PropertyUtility.js'
import RelationUtility from './RelationUtility.js'
import ActionUtility from './ActionUtility.js'

export default class Node extends CustomElement(HTMLElement){
	
	#board
	#table
	#status
	
	#propertyList
	#relationList
	#actionList
	
	#controls
	
	//static get baseTag(){ return 'form' }
	
	get board(){ return this.#board }
	set board(value){ this.#board = value }
	
	get table(){ return this.#table.innerHTML }
	set table(value){ this.#table.innerHTML = value }
	
	get status(){ return this.#status.innerHTML }
	set status(value){ this.#status.innerHTML = value }
	
	get propertyList(){ return this.#propertyList }
	set propertyList(value){ this.#propertyList = value }
			
	get relationList(){ return this.#relationList }
	set relationList(value){ this.#relationList = value }
	
	get actionList(){ return this.#actionList }
	set actionList(value){ this.#actionList = value }
	
	addStatus(text,colorClass){
		this.#status.append( ElementUtility.create('div',text,['badge','px-2', 'py-1', colorClass]) )
	}
	
	constructor() { 
		super()
		
		this.#table = this.querySelector('label') ?? document.createElement('label')
		this.#table.classList.add('text-light','h4','p-2')
		this.#table.setAttribute('contenteditable','true')
		
		this.#status = document.createElement('div')
		this.#status.classList.add('d-flex','flex-row','gap-2','py-2')
		
		this.#propertyList = this.querySelector('devworx-properties') ?? ElementUtility.create(
			'devworx-properties',
			{type:'Property'},
			['d-flex','flex-column','col-12','p-3','border','border-light']
		)
		
		this.#relationList = this.querySelector('devworx-relations') ?? ElementUtility.create(
			'devworx-relations',
			{type:'Relation'},
			['d-flex','flex-column','col-12','p-3','border','border-light']
		)
		
		this.#actionList = this.querySelector('devworx-actions') ?? ElementUtility.create(
			'devworx-actions',
			{type:'Action'},
			['d-flex','flex-column','col-12','p-3','border','border-light']
		)
    }
	
	init(){
		super.init()	
		
		this.classList.add('d-flex','flex-column','p-2')
				
		this.append( 
			this.#table, 
			this.#status,
			this.#propertyList, 
			this.#relationList,
			this.#actionList
		)
		return this
	}
	
	load(
		board,
		table=undefined,
		properties=undefined,
		relations=undefined,
		actions=undefined
	){
		this.board = board
		if( board ) board.append( this )
		if( table ) this.table = table
		if( properties ) this.propertyList.load(properties)
		if( relations ) this.relationList.load(relations)
		if( actions ) this.actionList.load(actions)
		return this
	}
	
	get value(){
		return {
			table: this.#table.innerHTML.trim(),
			properties: this.#propertyList.value,
			relations: this.#relationList.value,
			actions: this.#actionList.value
		}
	}
}
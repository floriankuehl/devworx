import CustomElement from '/resources/devworx/Scripts/CustomElement.js'
import ElementUtility from '/resources/devworx/Scripts/ElementUtility.js'
import Api from '/resources/devworx/Scripts/Api.js'

import Node from './Node.js'
import PropertyUtility from './PropertyUtility.js'
import RelationUtility from './RelationUtility.js'
import ActionUtility from './ActionUtility.js'

export default class Board extends CustomElement(HTMLElement){
	
	#controls

	#activeNode
	#activeProperty
	#activeRelation
	#activeAction
	
	constructor() { 
		super()
		this.#controls = ElementUtility.create('nav',null,['d-flex','flex-row','gap-2','py-2'])
    }
	
	get activeNode(){ return this.#activeNode }
	set activeNode(value){
		if( this.#activeNode )
			this.#activeNode.removeAttribute('selected')
		if( value )
			value.setAttribute('selected','true')
		this.#activeNode = value
	}
	
	get activeProperty(){ return this.#activeProperty }
	set activeProperty(value){
		if( this.#activeProperty )
			this.#activeProperty.removeAttribute('selected')
		if( value )
			value.setAttribute('selected','true')
		this.#activeProperty = value
	}
	
	get activeRelation(){ return this.#activeRelation }
	set activeRelation(value){
		if( this.#activeRelation )
			this.#activeRelation.removeAttribute('selected')
		if( value )
			value.setAttribute('selected','true')
		this.#activeRelation = value
	}
	
	get value(){
		let data = {};
		[ ...this.querySelectorAll('devworx-node') ].forEach(node=>{
			data[ node.table ] = node.value
		})
		return data
	}
	
	async loadTable(tableName){
		return await Api.Get({controller:'Model',action:'schema'})
			.then(json=>{
				console.log( json )
				for( let table of Object.keys(json.table) ){
					this.loadNode( table, json[table] )
				}
				return json
			})
	}
	
	loadNode(tableName,info){
		return Node.createElement(
			node => node.load(
				this,
				tableName,
				info.properties,
				info.relations,
				info.actions
			)
		)
	}
	
	loadEvents(node){
		node.addEventListener('click',e=>{
			this.activeNode = node
		})
		node.querySelectorAll('devworx-property')
			.forEach(item=>{
				item.addEventListener('click',e=>{
					this.activeProperty = item
				})
			})
		node.querySelectorAll('devworx-relation')
			.forEach(item=>{
				item.addEventListener('click',e=>{
					this.activeRelation = item
				})
			})
		node.querySelectorAll('devworx-action')
			.forEach(item=>{
				item.addEventListener('click',e=>{
					this.activeAction = item
				})
			})
		return node
	}
	
	load(){
		this.querySelectorAll('devworx-node').forEach(node=>this.loadEvents(node))
		return this
	}
	
	init(){
		super.init()
		this.append( this.#controls )
	}
}
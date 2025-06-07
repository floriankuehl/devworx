import CustomElement from '../Devworx/CustomElement.js'
import ElementUtility from '../Devworx/ElementUtility.js'

import {PropertyUtility} from './Property.js'
import {RelationUtility} from './Relation.js'
import {ActionUtility} from './Action.js'

export default class Board extends CustomElement(HTMLElement){
	
	#controls
	#createProperty
	#createRelation
	#createAction
	
	#activeModel
	#activeProperty
	#activeRelation
	#activeAction
	
	constructor() { 
		super()
		
		this.#controls = ElementUtility.create('nav',null,['d-flex','flex-row','gap-2','py-2'])
		this.#createProperty = ElementUtility.create('button','Create Property',['btn','btn-primary'])
		this.#createRelation = ElementUtility.create('button','Create Relation',['btn','btn-primary'])
		this.#createAction = ElementUtility.create('button','Create Action',['btn','btn-primary'])
    }
	
	get activeModel(){ return this.#activeModel }
	set activeModel(value){
		if( this.#activeModel )
			this.#activeModel.removeAttribute('selected')
		if( value )
			value.setAttribute('selected','true')
		this.#activeModel = value
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
	
	loadEvents(node){
		node.addEventListener('click',e=>{
			this.activeModel = node
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
		
		this.#controls.append(
			this.#createProperty, 
			this.#createRelation,
			this.#createAction
		)
		
		this.#createProperty.addEventListener('click',e=>{
			const prop = PropertyUtility.Ask()
			if( prop )this.propertyList.fields.append(prop)
		})
		
		this.#createRelation.addEventListener('click',e=>{
			const relation = RelationUtility.Ask()
			if( relation ) this.relationList.append(relation)
		})
	
		this.#createAction.addEventListener('click',e=>{
			const action = ActionUtility.Ask()
			if( action ) this.actionList.append(action)
		})
	
		this.append(this.#controls)
	}
}
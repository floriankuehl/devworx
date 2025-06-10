import ElementUtility from '../Devworx/ElementUtility.js'
import List from '../Devworx/Elements/List.js'
import RelationUtility from './RelationUtility.js'

export default class Relations extends List {
	#controls
	#create
	#header
	
	constructor(){
		super()
		
		this.#controls = ElementUtility.create('nav','',['d-flex','flex-row','gap-2','py-2'])
		this.#create = ElementUtility.create('button','Create Relation',['btn','btn-primary'])
		this.#header = ElementUtility.create('div','',['d-flex','flex-row'])
	}
	
	init(){
		super.init()
		this.setAttribute('type','Relation')
		
		this.#header.append( ...RelationUtility.header() )
		
		this.#create.addEventListener('click',e=>{
			const relation = RelationUtility.Ask()
			if( relation ) this.append(relation)
		})
	
		this.#controls.append(
			this.#create
		)
		
		this.append( this.#controls, this.#header )
		
		return this
	}
	
	load(relations=undefined){
		if( relations )
			ElementUtility.html(
				this,
				this.#controls,
				this.#header,
				...RelationUtility.map(relations)
			)
	}
	
	get value(){
		return [...this.querySelectorAll('devworx-relation')].map(p=>p.value)
	}
}
import ElementUtility from '../Devworx/ElementUtility.js'
import List from '../Devworx/Elements/List.js'
import ActionUtility from './ActionUtility.js'

export default class Actions extends List {
	
	#controls
	#header
	#create
	
	constructor(){
		super()
		
		this.#controls = ElementUtility.create('nav','',['d-flex','flex-row'])
		this.#header = ElementUtility.create('div','',['d-flex','flex-row'])
		this.#create = ElementUtility.create('button','Create Action',['btn','btn-primary'])
	}
	
	init(){
		super.init()
		this.setAttribute('type','Action')
		
		this.#header.append(...ActionUtility.header());
			
		this.#create.addEventListener('click',e=>{
			const action = ActionUtility.Ask()
			if( action ) this.append(action)
		})
	
		this.#controls.append( this.#create )
		
		this.append( this.#controls, this.#header )
	
	}
	
	load(actions=undefined){
		if( actions )
			this.html(
				this.#controls,
				this.#header,
				...ActionUtility.map(actions)
			)
	}
	
	get value(){
		return [...this.querySelectorAll('devworx-action')].map(p=>p.value)
	}
}
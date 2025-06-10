import CustomElement from '../Devworx/CustomElement.js'
import ElementUtility from '../Devworx/ElementUtility.js'
import ActionUtility from './ActionUtility.js'

export default class Action extends CustomElement(HTMLElement) {
	#name
	#json
	#controls
	
	get name(){ return this.#name.value }
	set name(value){ this.#name.value = value }
	
	get json(){ return this.#json.checked }
	set json(value){ this.#json.checked = value }

	get controls(){ return this.#controls }
	set controls(value){ this.#controls = value }

	constructor(){
		super()
		
		this.#name = ElementUtility.create('input',{type:'text'})
		this.#json = ElementUtility.create('input',{type:'checkbox',value:1})
		this.#controls = ElementUtility.create('div','',['d-flex','flex-row'])
	}
	
	init(){
		super.init()
		
		if( this.hasAttribute('name') )
			this.name = this.getAttribute('name')
		if( this.hasAttribute('json') )
			this.json = this.getAttribute('json')
		
		this.append(
			ElementUtility.create('div',this.#name,['p-1',ActionUtility.css(0)]),
			ElementUtility.create('div',this.#json,['p-1',ActionUtility.css(1)]),
			ElementUtility.create('div',this.#controls,['p-1',ActionUtility.css(2)])
		)
		return this
	}
	
	get value(){
		return {
			name: this.#name.value,
			json: this.#json.checked
		}
	}
	
	json(){
		return JSON.stringify(this.value)
	}
}


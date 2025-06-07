import CustomElement from '../Devworx/CustomElement.js'
import List from '../Devworx/Elements/List.js'
import ElementUtility from '../Devworx/ElementUtility.js'

export class ActionUtility {
	
	static cols = [8,2,2]
	
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
			this.item('Action',0),
			this.item('JSON',1),
			this.item('...',2)
		]
	}
	
	static instance(data){
		return Action.createElement((item)=>{
			item.classList.add('d-flex','flex-row')
			if( typeof data === 'object' ){
				if( Array.isArray(data) ){
					item.setAttribute('name',data[0])
					item.setAttribute('json',data[1])
					return item
				}
				item.setAttribute('name',data.name)
				item.setAttribute('json',data.json ? 1 : 0)
				return item
			}
			item.setAttribute('name',data)
			return item
		})
	}
	
	static map(rows){
		return rows.map(row=>Array.isArray(row) ? this.instance(...row) : this.instance(row))
	}
	
	static Ask(name=null,json=null){
		if( name === null ) name = prompt('Action name')
		if( json === null ) json = confirm('JSON?')
		
		return Relation.createElement((item)=>{
			item.setAttribute('name',name)
			item.setAttribute('json',json ? 1 : 0)
		})
	}
}

export class Action extends CustomElement(HTMLElement) {
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

export class Actions extends List {
	constructor(){
		super()
	}
	
	init(){
		super.init()
		this.setAttribute('type','Action')
	}
	
	load(actions=undefined){
		if( actions )
			this.append(...ActionUtility.map(actions))
	}
	
	get value(){
		return [...this.querySelectorAll('devworx-action')].map(p=>p.value)
	}
}
import CustomElement from '/resources/devworx/Scripts/CustomElement.js'
import ElementUtility from '/resources/devworx/Scripts/ElementUtility.js'
import PropertyUtility from './PropertyUtility.js'

export default class Property extends CustomElement(HTMLElement){
	
	#name
	#key
	#phptype
	#dbtype
	#length
	#nullable
	#value
	#extra
	
	#moveUp
	#moveDown
	#remove
	
	//static get baseTag(){ return 'li' }
	
	constructor() { 
		super()
								
		this.#moveUp = ElementUtility.create('button','&uarr;',['btn'])
		this.#moveDown = ElementUtility.create('button','&darr;',['btn'])
		this.#remove = ElementUtility.create('button','x',['btn'])
		
		this.#name = ElementUtility.create('input',{type:'text'})
		this.#key = ElementUtility.create('select',PropertyUtility.keyOptions)
		this.#phptype = ElementUtility.create('select',PropertyUtility.phptypeOptions)
		this.#dbtype = ElementUtility.create('select',PropertyUtility.dbtypeOptions)
		this.#length = ElementUtility.create('input',{type:'number'})
		this.#value = ElementUtility.create('input',{type:'text'})
		this.#nullable = ElementUtility.create('input',{type:'checkbox',value:1})
		this.#extra = ElementUtility.create('input',{type:'text'})
    }
	
	set canMoveUp(state=true){
		this.#moveUp.classList[ state ? 'remove' : 'add' ]('invisible')
	}
	
	set canMoveDown(state=true){
		this.#moveDown.classList[ state ? 'remove' : 'add' ]('invisible')
	}
	
	init(){
		super.init()
		//console.log( 'initialize', this.localName, this.getAttribute('name') )
		
		this.classList.add('d-flex','flex-row');
		
		const variable = 'properties'
		this.#name.setAttribute('name',`${variable}[name][]`)
		this.#name.setAttribute('value', this.getAttribute('name') ?? '')
		
		this.#key.setAttribute('name',`${variable}[key][]`)
		this.#key.value = this.getAttribute('key') ?? ''
		
		this.#phptype.setAttribute('name',`${variable}[phptype][]`)
		this.#phptype.value = this.getAttribute('phptype') ?? ''
		
		this.#dbtype.setAttribute('name',`${variable}[dbtype][]`)
		this.#dbtype.value = this.getAttribute('dbtype') ?? ''
		
		this.#length.setAttribute('name',`${variable}[length][]`)
		this.#length.value = this.getAttribute('length') ?? ''
		
		this.#value.setAttribute('name',`${variable}[value][]`)
		this.#value.setAttribute('value', this.getAttribute('value') ?? '')
		
		this.#nullable.setAttribute('name',`${variable}[nullable][]`)
		this.#nullable.setAttribute('value', 1)
		this.#nullable.checked = this.hasAttribute('nullable') && this.getAttribute('nullable') == 1
		
		this.#extra.setAttribute('name',`${variable}[extra][]`)
		this.#extra.setAttribute('value', this.getAttribute('extra') ?? '')
		
		this.#moveUp.addEventListener('click',e=>{
			this.previousSibling.before( this )
			this.classList.add('active')
			PropertyUtility.updateMovables(this.parentNode)
			setTimeout(_=>this.classList.remove('active'),500)
		})
		
		this.#moveDown.addEventListener('click',e=>{
			this.nextSibling.after(this)
			this.classList.add('active')
			PropertyUtility.updateMovables(this.parentNode)
			setTimeout(_=>this.classList.remove('active'),500)
		})
		
		this.#remove.addEventListener('click',e=>{
			const p = this.parentNode
			this.remove()
			PropertyUtility.updateMovables(p)
		})
		
		this.append(
			ElementUtility.create('div',this.#name,['p-1',PropertyUtility.css(0)]), 
			ElementUtility.create('div',this.#key,['p-1',PropertyUtility.css(1)]),
			ElementUtility.create('div',this.#phptype,['p-1', PropertyUtility.css(2)]),
			ElementUtility.create('div',this.#dbtype,['p-1', PropertyUtility.css(3)]),
			ElementUtility.create('div',this.#length,['p-1', PropertyUtility.css(4)]),
			ElementUtility.create('div',this.#value,['p-1', PropertyUtility.css(5)]), 
			ElementUtility.create('div',this.#nullable,['p-1', PropertyUtility.css(6)]),
			ElementUtility.create('div',this.#extra,['p-1', PropertyUtility.css(7)]),
			ElementUtility.create('div',[this.#moveUp,this.#moveDown,this.#remove],['p-1','d-flex','flex-row','gap-2',PropertyUtility.css(8)])
		)
		
		if( this.parentNode ) PropertyUtility.updateMovables(this.parentNode)
	}
	
	get value(){
		return {
			name: this.#name.value,
			key: this.#key.value,
			phpType: this.#phptype.value,
			dbType: this.#dbtype.value,
			length: this.#length.value,
			value: this.#value.value,
			nullable: this.#nullable.checked,
			extra: this.#extra.value
		}
	}
	
	json(){
		return JSON.stringify(this.value)
	}
}


import ElementUtility from '/resources/devworx/Scripts/ElementUtility.js'
import List from '/resources/devworx/Scripts/Elements/List.js'
import PropertyUtility from './PropertyUtility.js'

export default class Properties extends List {
	
	#header
	#create
	#controls
	#remove
	
	#prepend
	#legendPrepend
	#append
	#legendAppend
	#fields
	#legendFields
	
	//static baseTag(){ return 'devworx-node' }
	
	constructor(){
		super()
		
		this.#header = ElementUtility.create('div','',['d-flex','flex-row'])
		this.#controls = ElementUtility.create('nav','',['d-flex','flex-row','py-2']),
		this.#create = ElementUtility.create('button','Create Property',['btn','btn-primary'])
		
		this.#legendPrepend = ElementUtility.create('legend','Index',['h5'])
		this.#legendAppend = ElementUtility.create('legend','System',['h5'])
		this.#legendFields = ElementUtility.create('legend','Fields',['h5'])
		
		this.#prepend = ElementUtility.create('fieldset','',['d-flex','flex-column'])
		this.#append = ElementUtility.create('fieldset','',['d-flex','flex-column'])
		this.#fields = ElementUtility.create('fieldset','',['d-flex','flex-column'])
	}
	
	load(properties=undefined){
		if( properties ){
			const data = PropertyUtility.model(properties)
			
			ElementUtility.html(this.#prepend,this.#legendPrepend,...data.prepend)
			ElementUtility.html(this.#fields,this.#legendFields,...data.fields)
			ElementUtility.html(this.#append,this.#legendAppend,...data.append)
			
			this.disableProperties(this.#append)
			this.disableProperties(this.#prepend)
		}
	}
	
	disableProperties(item){
		item.setAttribute('disabled','true')
		item.querySelectorAll('input, select, textarea').forEach(i=>{
			i.classList.add('border-0')
			i.setAttribute('readonly','true')
			i.setAttribute('disabled','true')
		})
		item.querySelectorAll('button').forEach(i=>i.remove())
		return item
	}
	
	init(){
		super.init()
		this.setAttribute('type','Property')
		
		this.#header.append(...PropertyUtility.header())
		
		this.#prepend.append( this.#legendPrepend )
		this.#append.append( this.#legendAppend )
		this.#fields.append( this.#legendFields )
				
		this.#create.addEventListener('click',e=>{
			const prop = PropertyUtility.Ask()
			if( prop )this.#fields.append(prop)
		})
		
		this.#controls.append(
			this.#create
		)
		
		this.append(
			this.#controls,
			this.#header,
			this.#prepend,
			this.#fields,
			this.#append
		)
		
	}
	
	get value(){
		return [...this.querySelectorAll('devworx-property')].map(p=>p.value)
	}
}
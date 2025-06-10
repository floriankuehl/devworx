import CustomElement from '../Devworx/CustomElement.js'
import ElementUtility from '../Devworx/ElementUtility.js'

export default class Table extends CustomElement(HTMLElement) {
	#name
	#properties
	#controller
	#actions
	#model
	#repository
	#template
	
	#badges
	
	constructor(){
		super()
		
		this.#name = ElementUtility.create('strong','',['h3'])
		this.#properties = ElementUtility.create('span','Properties',['badge','text-bg-info','p-2'])
		this.#actions = ElementUtility.create('span','Actions',['badge','text-bg-info','p-2'])
		this.#controller = ElementUtility.create('span','Controller',['badge','p-2'])
		this.#model = ElementUtility.create('span','Model',['badge','p-2'])
		this.#repository = ElementUtility.create('span','Repository',['badge','p-2'])
		this.#template = ElementUtility.create('span','Template',['badge','p-2'])
		
		this.#badges = ElementUtility.create('div','',['d-flex','flex-row'])
	}
		
	init(){
		super.init()
		
		this.classList.add('d-flex','flex-column','p-3')
		
		if( this.hasAttribute('name') )
			this.#name.innerHTML = this.getAttribute('name')
		
		if( this.hasAttribute('properties') )
			this.#properties.innerHTML = this.getAttribute('properties') + ' Fields'
		
		if( this.hasAttribute('actions') )
			this.#actions.innerHTML = this.getAttribute('actions') + ' Actions'
		
		if( this.hasAttribute('controller') ){
			const hasController = parseInt(this.getAttribute('controller')) > 0
			this.#controller.classList.add('text-bg-' + ( hasController ? 'success' : 'warning' ))
		}
		
		if( this.hasAttribute('model') ){
			const hasModel = parseInt(this.getAttribute('model')) > 0
			this.#model.classList.add('text-bg-' + ( hasModel ? 'success' : 'warning' ))
		}
		
		if( this.hasAttribute('repository') ){
			const hasRepository = parseInt(this.getAttribute('repository')) > 0
			this.#repository.classList.add('text-bg-' + ( hasRepository ? 'success' : 'warning' ))
		}
		
		if( this.hasAttribute('template') ){
			const hasTemplate = parseInt(this.getAttribute('template')) > 0
			this.#template.classList.add('text-bg-' + ( hasTemplate ? 'success' : 'warning' ))
		}

		this.#badges.append(
			this.#properties,
			this.#controller,
			this.#actions,
			this.#model,
			this.#repository,
			this.#template
		)
		
		this.append(
			this.#name,
			this.#badges
		)
		return this
	}
}


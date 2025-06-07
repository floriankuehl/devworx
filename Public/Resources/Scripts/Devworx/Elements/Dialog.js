import CustomElement from '../CustomElement.js'

export default class Dialog extends CustomElement(HTMLElement) {
   
	#elements = {}

	constructor() { 
		super()
	}
  
	init() {
		this.setAttribute('devworx-dialog','')
		this.classList.add(
		  'd-none',
		  'position-fixed',
		  'col-12','col-md-8','col-lg-6',
		  'z-5',
		  'border','border-dark','rounded',
		  'shadow'
		)
		this
		  .createHeader()
		  .createBody()
		  .createFooter()
	}
  
	get elements(){
		return this.#elements
	}

	get hidden(){
		return this.classList.contains('d-none')
	}

	get visible(){
		return !this.hidden
	}
  
	show(){
		if( this.hidden )
		  this.classList.remove('d-none')
		return this
	}
  
	close(){
		if( this.visible )
		  this.classList.add('d-none')
		return this
	}

	center(){
		this.x = Math.floor( ( innerWidth - this.w ) * .5 )
		this.y = Math.floor( ( innerHeight - this.h ) * .5 )
		return this
	}
  
	createHeader(){

		const index = this.#elements

		let result = this.querySelector(':scope > [dialog-header]')
		if( !result ){
		  result = document.createElement('header')
		  result.setAttribute('dialog-header','')
		  result.classList.add('d-flex','flex-row','flex-shrink-0','text-bg-primary')
		  
		  const icon = document.createElement('span')
		  icon.classList.add('mi','mi-outline','flex-shrink-0')
		  icon.setAttribute('dialog-icon','')
		  index.icon = icon
		  
		  const title = document.createElement('span')
		  title.setAttribute('dialog-title','')
		  title.classList.add('d-flex','flex-row','flex-grow-1','py-2')
		  index.title = title
		  
		  const controls = document.createElement('div')
		  controls.setAttribute('dialog-controls','')
		  controls.classList.add('d-flex','flex-row','flex-shrink-0')
		  index.controls = controls
		  
		  const close = document.createElement('button')
		  close.setAttribute('dialog-close','')
		  close.innerHTML = 'X'
		  close.classList.add('btn','btn-primary')
		  controls.append(close)
		  close.addEventListener('click',e=>{
			e.stopPropagation()
			e.preventDefault()
			this.close()
		  })
		  index.close = close
		  
		  result.append(icon,title,controls)
		  this.append(result)
		}
		index.header = result

		return this
	}
  
	createBody(){
		const index = this.#elements

		let result = this.querySelector(':scope > [dialog-body]')
		if( !result ){
		  result = document.createElement('div')
		  result.classList.add('d-flex','flex-column','p-3','flex-grow-1','bg-white')
		  result.setAttribute('dialog-body','')
		  this.append(result)
		}
		index.body = result

		return this
	}
  
	createFooter(){
		const index = this.#elements
		let result = this.querySelector(':scope > [dialog-footer]')
		if( !result ){
		  result = document.createElement('footer')
		  result.classList.add('d-flex','flex-row')
		  result.setAttribute('dialog-footer','')
		  
		  const status = document.createElement('span')
		  status.setAttribute('dialog-status','')
		  status.classList.add('d-flex','flex-grow-1')
		  index.status = status
		  result.append(status)
		  
		  this.append(result)
		}
		index.footer = result

		return this
	}
  
	getIcon(){
		return this.#elements.icon
	}

	setIcon(value){
		this.#elements.icon.innerHTML = value
	}

	getTitle(){
		return this.#elements.title
	}

	setTitle(value){
		this.#elements.title.innerHTML = value
	}

	getBody(){
		return this.#elements.body
	}

	setBody(value){
		this.#elements.body.innerHTML = value
	}

	getStatus(){
		return this.#elements.status
	}

	setStatus(value){
		this.#elements.status.innerHTML = value
	}

	clear(){
		this.setTitle('')
		this.setBody('')
		this.setStatus('')
	}
  
}
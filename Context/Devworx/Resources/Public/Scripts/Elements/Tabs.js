import CustomElement from '../CustomElement.js'

export default class Tabs extends CustomElement(HTMLDivElement) {
	#lists
	#triggers
	#active = 0

	static get baseTag(){ return 'div' }
	constructor() { 
		super()
	}
  
	set active(value){
		if( this.#active == value ) 
		  return
		this.#lists[this.#active].classList.add('d-none')
		this.#active = value
		this.#triggers.forEach(el=>el.classList.remove('active'))
		this.#triggers[this.#active].classList.add('active')
		this.#lists[this.#active].classList.remove('d-none')
	}

	get active(){
		return this.#active
	}

	init() {  
		this.#active = 0
		this.#lists = this.querySelectorAll(':scope > tabs > *');
		this.#triggers = this.querySelectorAll(':scope > nav > *')

		let active = false
		this.#triggers.forEach((el,i)=>{
		  if( el.classList.contains('active') && (active === false) ){
			active = i
		  } else {
			this.#lists[i].classList.add('d-none')
			el.classList.remove('active')
		  }
		})
		   
		this.#triggers.forEach( (el,i) => {
		  el.addEventListener('click',e => {
			e.preventDefault()
			e.stopPropagation()
			this.active = i;
		  })
		})
		if( active === false ){
		  this.active = 0
		}
	}
  
}
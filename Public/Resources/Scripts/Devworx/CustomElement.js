import CustomElements from './ElementUtility.js'

export default function CustomElement(Base) {
  return class extends Base {
	#ready = false
	
	static namespace = 'devworx'
	static get baseTag(){ return false }
	static get elementTag() { return `${this.namespace}-${this.name.toLowerCase()}` }
	static get elementOptions() { return this.baseTag ? { extends: this.baseTag } : undefined }
	
	static createElement(callback=null){ return CustomElements.instance(this,callback) }
	static register(){ return CustomElement.registerClass(this)	}
	
	set timeout(func){ setTimeout(func,100)	}

	get rect(){
		return {
		  x: this.offsetLeft, 
		  y: this.offsetTop, 
		  w: this.offsetWidth, 
		  h: this.offsetHeight
		}
	}

	get x(){ return this.offsetLeft }
	set x(value){ this.style.left = `${value}px` }
	get y(){ return this.offsetTop }
	set y(value){ this.style.top = `${value}px` }
	get w(){ return this.offsetWidth }
	set w(value){ this.style.width = `${value}px` }
	get h(){ return this.offsetHeight }
	set h(value){ this.style.height = `${value}px` }
	
	get salary(){
		let result = .0
		this.querySelectorAll('devworx-salary')
		  .forEach(s=>{
			const v = parseFloat(s.getAttribute('value'))
			result += isNaN(v) ? 0 : v;
		  })
		return result
	}

	get timespan(){
		let result = .0
		this.querySelectorAll('devworx-timespan')
		  .forEach(s=>{
			const v = parseFloat(s.getAttribute('total-hours'))
			result += isNaN(v) ? 0 : v;
		  })
		return result
	}

	find(selector,iterator){
		const result = this.querySelectorAll(selector)
		if( iterator ) result.forEach(iterator)
		return result
	}
	
	init(){
		this.#ready = true
		return this
	}
	
	connectedCallback() {
		return this.#ready ? this : this.init()
	}

	disconnectedCallback() {
		this.#ready = false
		//console.log(`${this.elementTag} removed from page`);
	}

	connectedMoveCallback() {
		//console.log(`${this.elementTag} moved`);
	}

	adoptedCallback() {
		//console.log(`${this.elementTag} moved to new page`);
	}

	attributeChangedCallback(name, oldValue, newValue) {
		//console.log(`${this.elementTag}.${name} changed`);
	}
  }
}
export default function (Base) {
  return class extends Base {
	#ready = false
	
	static namespace = 'devworx'
	static get baseTag(){ return false }
	static get elementTag() { return `${this.namespace}-${this.name.toLowerCase()}` }
	static get elementOptions() { return this.baseTag ? { extends: this.baseTag } : undefined }
	
	static createElement(callback=null){
		let result = customElements.get(this.elementTag) ? 
			document.createElement(this.elementTag) :
			document.createElement(this.baseTag,{is:this.elementTag})
		if( callback ) callback(result,this)
		return result
	}
	
    static register() {
		//console.log( 'register', this.elementTag )
		customElements.define(this.elementTag, this, this.elementOptions);
		return customElements.get(this.elementTag)
    }
	
	create(
		tag,
		attributes=undefined,
		classes=undefined,
		callback=undefined
	){
		const result = document.createElement(tag)
		if( attributes ){
			switch( typeof attributes ){
				case'object':{
					if( Array.isArray(attributes) ){
						if( typeof attributes[0] === 'object' )
							result.append(...attributes)
						else
							result.classList.add(...attributes)
					} else if( classes instanceof HTMLCollection ){
						result.append(...HTMLCollection)
					} else if( attributes instanceof HTMLElement ) {
						result.append(attributes)
					} else {
						for( let k of Object.keys(attributes) )
							result.setAttribute(k,attributes[k])
					}
				}break
				case'string':{
					result.innerHTML += attributes
				}break;
				case'function':{
					return attributes(result)
				}break
			}
		}
		if( classes ){
			switch( typeof classes ){
				case'object':{
					if( Array.isArray(classes) ){
						if( typeof classes[0] === 'object' ){
							result.append(...classes)
						} else {
							result.classList.add(...classes)
						}
					} else if( classes instanceof HTMLCollection ){
						result.append(...classes)
					} else if( classes instanceof HTMLElement ){
						result.append(classes)
					} else {
						for( let k of Object.keys(classes) )
							result.style[k] = classes[k]
					}
				}break
				case'string':{
					result.innerHTML += classes
				}break;
				case'function':{
					return classes(result)
				}break
			}
		}
		return callback ? callback(result) : result
	}
	
	set timeout(func){
		setTimeout(func,100)
	}

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
	}
	
	connectedCallback() {
		if( !this.#ready )
			this.init()
	}

	disconnectedCallback() {
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
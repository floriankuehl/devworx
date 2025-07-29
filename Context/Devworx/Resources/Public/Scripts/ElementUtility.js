export default class ElementUtility {
	
	static debug = false
	
	static html(element,...list){
		element.innerHTML = ''
		element.append(...list)
		return element
	}
	
	static getExtend(constructor) {
		let proto = constructor.prototype;
		let prev = null;

		while (proto && proto !== HTMLElement.prototype) {
			prev = proto;
			proto = Object.getPrototypeOf(proto);
		}

		if (!prev) return null;
		return prev.constructor.name;
	}
	
	static isCustomElement(element){
		return ( typeof element === 'function' ) &&
			( element.prototype instanceof HTMLElement ) &&
			( typeof element.register === 'function' ) && 
			( typeof element.namespace === 'string' ) &&
			( typeof element.elementTag === 'string' )
	}
	
	static registerCustom(elementClass){
		let element = null
		if ( 
			this.isCustomElement(elementClass) && 
			!customElements.get(elementClass.elementTag)
		){
			//console.log( 'register', this.elementTag )
			customElements.define(
				elementClass.elementTag, 
				elementClass, 
				elementClass.elementOptions
			);
			element = customElements.get(elementClass.elementTag)
			if( this.debug )
				console.log(`${element?'Success':'Failed'} adding ${this.getExtend(elementClass)} ${elementClass.name} as ${elementClass.elementTag}`);
		}
		return element
	}
	
	static registerModule(module) {
		return Object.keys(module).map(
			className => this.registerCustom(module[className])
		)
	}
	
	static registerModules(...modules) {
		return modules.map(module=>this.registerModule(module))
	}
	
	static instance(elementClass,callback=undefined){
		let result = customElements.get(elementClass.elementTag) ? 
			document.createElement(elementClass.elementTag) :
			document.createElement(elementClass.baseTag,{is:staticElement.elementTag})
		if( callback ) callback(result,elementClass)
		return result
	}
	
	static create(
		tag,
		attributes=undefined,
		classes=undefined,
		events=undefined,
		callback=undefined
	){
		const result = document.createElement(tag)
		if( attributes ){
			switch( typeof attributes ){
				case'string':{ result.innerHTML += attributes }break;
				case'function':{ return attributes(result) }break
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
						for( let k of Object.keys(attributes) ){
							const value = attributes[k]
							if( typeof value === 'function' )
								result.addEventListener(k,e=>value(e))
							else if( typeof value !== 'object' )
								result.setAttribute(k,attributes[k])
						}
					}
				}break
			}
		}
		
		if( classes ){
			switch( typeof classes ){
				case'function':{ return classes(result) }break
				case'string':{ result.innerHTML += classes }break;
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
			}
		}
		
		if( events ){
			switch( typeof events ){
				case'function':{ return events(result) }break
				case'string':{ result.innerHTML += events }break;
				case'object':{
					if( Array.isArray(events) ){
						
					} else if( events instanceof HTMLCollection ){
						result.append(...events)
					} else if( classes instanceof HTMLElement ){
						result.append(events)
					} else {
						for( let k of Object.keys(events) )
							result.addEventListener(k,e=>events[k](e))
					}
				}break
			}
		}
		
		return callback ? callback(result) : result
	}
	
	static option(value,label=undefined){
		return this.create('option',{value:value},label??value)
	}
	
	static options(...args){
		const result = []
		for( let data of args ){
			
			if( typeof data === 'string' || typeof data === 'number' ){
				result.push( this.option(data) )
				continue
			}
			
			if( Array.isArray(data) ){
				result.push( 
					...data.map(o=>{
						if( typeof o === 'string' || typeof o === 'number' || typeof o === 'boolean' ){
							return this.option(o)
						}
						if( typeof o === 'object' ){
							if( Array.isArray(o) )
								return this.option(...o)
							return this.option(o.value,o.label)
						}
					}) 
				)
				continue
			}
			
			if( data instanceof HTMLElement ){
				result.push( data )
				continue
			}
			
			if( typeof data === 'object' ){
				for( let k in data ){
					const list = data[k]
					
					if( typeof list === 'string' || typeof list === 'number' || typeof list === 'boolean' ){
						result.push( this.option(k,list) )
						continue
					}
					
					if( Array.isArray(list) ){
						result.push(
							this.create(
								'optgroup',
								{label:k},
								list.map(o=>this.option(o))
							)
						)
						continue
					}
				}
			}
		}
		return result
	}
}